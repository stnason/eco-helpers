<?php

namespace ScottNason\EcoHelpers\Traits;

use App\Http\Controllers\ProgressController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * This trait is used in any Controller that needs to include the file upload option.
 *
 * Include "use App/Traits/ehFileUpload" in the Controller Class declaration.
 *
 */
trait ehFileUpload
{

    // Total counters and returned message array
    protected $c = [
        'files_to_process' => 0,            // Total number of files in selected to process for this task.
        'files_processed' => 0,             // Number of files processed so far.
        'files_valid' => 0,                 // Number of files moved to the processed folder.
        'files_invalid' => 0,               // Number of files moved to the invalid folder.
        'total_lines_to_process' => 0,      // Count of all the lines in all of the files to process.
        'total_lines_processed' => 0,       // Total lines read so far from all files processed.
        'file_lines_to_process' => 0,       // Count of the lines in the currently processing file.
        'file_lines_processed' => 0,        // Total lines read so far in the currently processing file.
        'records_inserted' => 0,            // How many records have been inserted into the database or table.
        'records_modified' => 0,            // How many records have been edited or modified.
        'total_percent_complete' => 0,      // Calculated percentage complete for all files.
        'file_percent_complete' => 0,       // Calculated percentage complete for the currently processing file.
        'file_names_all' => [],             // An array of base file names for all files passed.
                                            // -Built in the count lines loop; $key(file_name)=>$value(lines) pair.
        'file_names_valid' => [],           // An array of base file names for the valid files.
        'file_names_invalid' => [],         // An array of base file names for the invalid files.
        'error_message' => '',              // Any problem(s) encountered with this processing loop.
    ];

     protected $progress_output = [           // The array of progress messages (title, message, percent, stop_polling)
        'title' => 'Title not set',
        'message' => '<div class="loading">Loading</div>',
        'percent' => 0,
        'stop_polling' => false
    ];

    protected $count_lines = true;                // Use the internal count lines routine; memory issues with large file so turn off.
                                                  // Use in conjunction setTotalLinesToProcess();
    protected $set_time_limit = 420;              // Set some reasonable processing script time limit
    protected $memory_limit = 512;                // Set some needed memory limit

    protected $storage_disk     = '';             // Root folder for the file upload.
    protected $processed_disk   = 'processed';    // Move files here after successfully processing.
    protected $invalid_disk     = 'invalid';      // Move invalid files here.
    protected $log_channel      = '';             // The defined logs channel to use.
    protected $progress_file_name = '';           // This should be set up in the main controller page

    // ?? is now $this
    //protected $import;                          // The import object for the Excel operations.

    protected $rows_to_burn     = 0;              // Skip the first 2 rows of data. (row 1 is blank; row 2 is the headers)

    // For files with a lot of lines - don't write an update on every read.
    protected $ajax_display_counter = 0;          // Counter to decide when to push an Ajax progress display
    protected $ajax_display_max = 25;             // Count to this number before pushing an Ajax progress display (50 is pretty good; display updates about once a second)

    protected $valid_extensions = '';             // A comma separated list; "csv, xlsx, docx"
    protected $fields_to_skip = [];


    /**
     * Public setter for the script time limit (in whole seconds)
     *
     * @param $seconds
     */
    public function setTimeLimit($seconds) {
        $this->set_time_limit = $seconds;
    }
    public function setMemoryAllocation($megabytes) {
        $this->memory_limit = $megabytes;
    }
    public function setStorageDisk($storage_disk) {
        $this->storage_disk = $storage_disk;
    }
    public function setProcessedDisk($processed_disk) {
        $this->processed_disk = $processed_disk;
    }
    public function setInvalidDisk($invalid_disk) {
        $this->invalid_disk = $invalid_disk;
    }
    public function setLogChannel($log_channel) {
        $this->log_channel = $log_channel;
    }
    public function setProgressFileName($progress_file_name) {
        $this->progress_file_name = $progress_file_name;
    }
    public function setRowsToBurn($rows_to_burn) {
        $this->rows_to_burn = $rows_to_burn;
    }
    public function setAjaxUpdateCounter($ajax_display_counter) {
        $this->ajax_display_counter = $ajax_display_counter;
    }
    public function setAjaxUpdateMax($ajax_display_max) {
        $this->ajax_display_max = $ajax_display_max;
    }
    public function setValidExtensions($valid_extensions) {
        // Note: as an array ['csv','xlsx','docx']
        $this->valid_extensions = $valid_extensions;
    }
    public function setFieldsToSkip($fields_to_skip) {
        // Note: as an array ['IP','MAC','hostname']
        $this->fields_to_skip = $fields_to_skip;
    }
    public function setProgressTitle($progress_title) {
        $this->progress_output['title'] = $progress_title;
    }
    public function setProgressMessage($progress_message) {
        $this->progress_output['message'] = $progress_message;
    }
    public function setCountLines($count_lines=true) {
        $this->count_lines = $count_lines;
    }
    public function setLinesToProcess($lines_to_process) {
        // Estimated lines to process set in the Controller;
        // Use when setting count lines to off.
        $this->c['file_lines_to_process'] = $lines_to_process;
        $this->c['total_lines_to_process'] = $lines_to_process;
    }
    public function setProgressPercent($progress_percent = 0) {
        $this->progress_output['percent'] = $progress_percent;
    }

    /**
     * Before attempting an insert into the database, dates need to be sql-ized.
     * But Carbon will gag if they're not real date formats so test it first
     *  and just return the original $data_field if it's not a recognizable date.
     *
     * @param $data_field
     * @param bool $with_datetime
     * @return string
     */
    protected function parseDate($data_field, $with_time=false) {

        //WARNING: This will return today's date if the date is blank!!
        if (empty($data_field)) {
            return $data_field;         // If blank or empty then just return the input data.
        }

        try {
            Carbon::parse($data_field);
            if ($with_time) {
                return Carbon::parse($data_field)->format('Y-m-d H:i:s');
            } else {
                return Carbon::parse($data_field)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            return $data_field;         // Doesn't appear to be a date so just return the input data.
        }

    }


    /**
     * Ajax processing for the any Controller that needs a file upload.
     *
     * Remember that users will need permissions to both the calling page and to the Controller@upload path too.
     * Note: That this is a standalone ajax call here so it will have to pass the storage disk;
     *       No $this variables are available since this is a completely new instance.
     */
    public function upload()
    {

        // Not sure how big a file you may have so you can override these in the processInitialize()
        //set_time_limit($this->set_time_limit);
        //ini_set("memory_limit",$this->ini_set."M");


        // public function upload(Document $document) {     // Include (inject) the model if you need to make changes below.

        // Note: stubbed out the 'data' portion of the return response
        //  but probably going to end up using just a page refresh instead (to redraw missing links or change link types).


        /* So it looks like $this->storage_disk is not defined here; (because it's an ajax call?)
        if (empty($this->storage_disk)) {
            $this->storage_disk = '_uploads';
        }
        */


        // So we do need to set up the storage_disk in the controller and then pass it to the template
        // so the js can use it to post back here.

        // Pull from the request(), the storage disk to use under storage/app.
        if (!empty(request()->storage_disk)) {
            $this->storage_disk = request()->storage_disk;
        } else {
            $this->storage_disk = '_uploads';
        }



        // If the calling program posts file_list_only then just return the file list here and do nothing else.
        if (!empty(request()->file_list_only)) {
            ////////////////////////////////////////////////////////////////////////////////////////////
            // Generate a list of files already in this location.
            //$output['file_list'] = Storage::files($absolute_storage_path);
            $output['file_list'] = Storage::disk($this->storage_disk)->files();
            echo json_encode($output);
            return;
        }

        // Setup the JSON return object
        $output = [
            'message' => '',
            'file_list' => [],
            'data' => [
                /* Any changes or data needed by the calling form.
                    'field_name'=>'',
                    'field_extension'=>'',
                    'updated_by'=>'',
                    'updated_at'=>'',
                */
            ]

        ];


        // DEAL WITH THE FILE UPLOADED FROM THE CLIENT FORM (posted as $_FILES[]).
        if (!empty($_FILES)) {

            /* Note: The posted / uploaded file array contains these fields.
            $_FILES['file'][
                'name',
                'type',
                'tmp_name',
                'error',
                'size'
                ]
            */

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Use pathinfo to break apart the file['name'] from above.
            $path_parts = pathinfo($_FILES['file']['name']);
            // $path_parts['basename'];        // This is the whole file name (no path) of the original uploaded file. (w/ extension)
            // $path_parts['filename'];        // The filename w/o the file extension.
            // $path_parts['extension'];       // This is the uploaded file's file extension.


            ////////////////////////////////////////////////////////////////////////////////////////////
            // 1. Build out the file and path names.

            // Get the complete absolute path to the storage folder for this disk.
            $absolute_storage_path = self::getAbsolutePathFromDisk($this->storage_disk);


            // Name of the original file selected for upload from the client.
            $original_filename = $path_parts['basename'];


            // Build the new file name (or use the incoming one "as is")
            // $new_filename = $document->id.'.'. $path_parts['extension'];
            $new_filename = $path_parts['filename'] . '.' . $path_parts['extension'];




            // Check for duplicate file names. ( but there is a method in here for that - chkDuplicateFilename(); )
            /* Old manual check -- using the new method chkDuplicateFilename() now.
            $num = 1;
            while (file_exists($absolute_storage_path . $new_filename)) {
                $new_filename = $path_parts['filename'] . '_' . $num . '.' . $path_parts['extension'];
                $num++;
            }
            */

            // chkDuplicateFilename($storage_disk, $intended_path_filename) {
            $new_filename = chkDuplicateFilename($this->storage_disk, $new_filename);


            // If there's a need to clean up by deleting previous files before moving the new file into place.
            // $delete_me_filename = $document->id.'.*';
            $delete_me_filename = null;

            // The name of the file and path as uploaded.
            $tmp_filename = $_FILES['file']['tmp_name'];


            /* This is before the file is moved. Not sure if we need this as a safety in case there's a tmp_file error below.
            ////////////////////////////////////////////////////////////////////////////////////////////
            // Generate a list of files already in this location.
            //$output['file_list'] = Storage::files($absolute_storage_path);
            $output['file_list'] = Storage::disk($this->storage_disk)->files();
            */

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Move and rename the tmp_file; And process and database updates needed.
            // But Don't do anything else if the file didn't get up to the server ($tmp_filename)
            if (file_exists($tmp_filename)) {

                // 2. Delete this id.* (in case we're replacing one doc type with another)
                // Delete all file extensions with this first name.
                if ($delete_me_filename) {
                    foreach (glob($absolute_storage_path . $delete_me_filename) as $filename) {
                        unlink($filename);
                    }
                }

                ////////////////////////////////////////////////////////////////////////////////////////////
                // 3. Move the tmp upload file into the protected documents disk
                // This gives a file not found error on the tmp file?
                // Storage::disk($this->storage_disk)->move($tmp_filename, $new_filename);
                move_uploaded_file($tmp_filename, $absolute_storage_path . $new_filename);


                ////////////////////////////////////////////////////////////////////////////////////////////
                // Generate a list of files already in this location.
                //$output['file_list'] = Storage::files($absolute_storage_path);
                $output['file_list'] = Storage::disk($this->storage_disk)->files();


                ////////////////////////////////////////////////////////////////////////////////////////////
                // 4. Make any changes to the database.
                $this->processDatatbaseChanges();


                ////////////////////////////////////////////////////////////////////////////////////////////
                //$message = 'File <strong>' . $original_filename . '</strong> uploaded successfully.';
                $message = 'File <strong>' . $new_filename . '</strong> uploaded successfully.';
                $output['message'] = $message;
                echo json_encode($output);
                return;

            } else {

                ////////////////////////////////////////////////////////////////////////////////////////////
                // The $tmp upload file was not found.
                $message = 'Server error: missing upload file; ' . $tmp_filename;
                $output['message'] = $message;
                echo json_encode($output);
                return;

            }

        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // $_FILES was empty or not set.
        $message = 'Server error: no upload files found.';
        $output['message'] = $message;
        echo json_encode($output);
        return;

    }



    /**
     * This happens at the end of the file upload process.
     * This is here strictly for overriding on an individual Controller basis.
     * Sometimes on a successful upload you may want to make a change in a database table.
     */
    protected function processDatatbaseChanges () {
        ////////////////////////////////////////////////////////////////////////////////////////////
        // 4. Make any changes to the database.
        /*
        $document->file_name = $original_filename;              // Store the original file name for reference only.
        $document->file_extension = $path_parts['extension'];   // File extension is used to display icon and build id.ext filename.
        $document->save();
        */
    }



    /**
     * Standard utility to check for the existence of a file before copying.moving and then increment the name (_1, _2, etc.)
     * to keep from overwriting the original.
     *
     * NOTE: This usages Laravel's Storage Facade and must include a valid $this->storage_disk.
     *       So $intended_path_filename will be a path (+file name) that is relative to that Storage disk.
     *
     * NOTE: Storage->move will throw an error if the file already exists in the move-to location!
     *
     * @param $filename
     * @param $storage_disk
     * @param $intended_path_filename
     * @return string                       - this is the corrected file name only (without the path)
     */
    public function chkDuplicateFilename($storage_disk, $intended_path_filename) {

        $limit = 250;   // Set some kind of reasonable safety net to keep the while loop from running away.

        // Usage - call before doing something like this:  (so pass the same parameters?)
        // Storage::disk($this->storage_disk)->move($file_name, $this->invalid_disk.'/'.$file_name);

        /*
         // Get the complete absolute path to the storage folder for this disk.
            $absolute_storage_path = Storage::disk($storage_disk)->getDriver()->getAdapter()->getPathPrefix();
        */

        $path_parts = pathinfo($intended_path_filename);    // Separate the base filename from its extension
            // $path_parts['extension'];                    // Just using these directly below here.
            // $path_parts['filename'];                     // Just using these directly below here.
        $new_filename =  $path_parts['basename'];           // Start out assuming the file is okay.



        // Check for duplicate file names in the intended location path.
        // NOTE: This could also be used to check for "safe" filenames too? ( replace characters or whatever? )
        $num = 1;
        while (Storage::disk($storage_disk)->exists($path_parts['dirname'].'/'.$new_filename)) {
            $new_filename = $path_parts['filename'] . '_' . $num . '.' . $path_parts['extension'];
            $num++;
            // Safety net in case we have a runaway loop situation.
            if ($num > $limit) {
                throw new \Exception('chkDuplicateFilename loop failure. Too many iterations.');
            }
        }

        // Return either the original file name (if no duplicate), or the modified file name that is now not a duplicate.
        return $new_filename;
    }



    /**
     * Count the total number of lines in the file that is in the supplied (Laravel Storage) $storage_disk.
     *
     * @param $storage_disk
     * @param $file_name
     * @return int
     */
    public function countLines($storage_disk, $file_name) {
        $absolute_storage_path = self::getAbsolutePathFromDisk($storage_disk);
        $file = new \SplFileObject($absolute_storage_path.'/'.$file_name, 'r');
        $file->seek(PHP_INT_MAX);
        return $file->key();                    // Not sure why; the original code had +1; but this seems to be accurate without it.
    }



    /**
     * Return the complete (absolute) server path to the Storage disk specified.
     *
     * @param $storage_disk
     * @return mixed
     */
    public function getAbsolutePathFromDisk($storage_disk) {

        // This broke from Laravel 8 to Laravel 9
        //return Storage::disk($storage_disk)->getDriver()->getAdapter()->getPathPrefix();

        // Works in Laravel 9
        return Storage::disk($storage_disk)->path('');

    }




    /**
     * A loop to process all uploaded files passed in the $frm_file[] array;
     *  - Check for some valid data condition then then import its data line by line.
     *
     * @param $frm_file
     * @return array
     */
    //protected function loopUploadedFiles($progress_file_name, $frm_file)
    public function loopUploadedFiles($frm_file)
    {

        // Not sure how big a file you may have so you can override these in the processInitialize()
        set_time_limit($this->set_time_limit);
        ini_set("memory_limit",$this->memory_limit."M");


        ///////////////////////////////////////////////////////////////////////////////////////////
        // No files were selected or available to process.
        // Note: js may be catching this on the front-side but just setting a safety net here.
        if (empty($frm_file)) {
            $progress_output['message'] = 'No files selected to process.';      // Written to the progress file.
            $this->c['error_message'] = 'No files selected to process.';        // Returned to calling method.
            goto end;
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // PROGRESS: Write this CURRENT PROGRESS out to the progress file.
        ProgressController::writeProgress($this->progress_file_name, $this->progress_output);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Grab the TOTAL NUMBER of files passed (Needed for the percentage complete calculation).
        $this->c['files_to_process'] = count($frm_file);



        ///////////////////////////////////////////////////////////////////////////////////////////
        // COUNT: the TOTAL LINES to process; Loop through all of the passed file names.
        foreach ($frm_file as $key => $file_name) {

            ///////////////////////////////////////////////////////////////////////////////////////////
            // Write this current progress out to the progress file.
            // Note: The .loading class is in the eco-orverride.css file and controls the ellipsis animation.
            $this->progress_output['message'] = '<div class="loading">Counting lines</div>';
            ProgressController::writeProgress($this->progress_file_name, $this->progress_output);

            ///////////////////////////////////////////////////////////////////////////////////////////
            // Count number of lines in all the files;

            // Seems to be more memory efficient--can load a bigger file--if we use the native
            // PhpSpreadsheet method to get the highest data row.
            // $data = Excel::toArray($this, $file_name, $this->storage_disk);
            // $file_line_cnt = count($data[0]);
            if ($this->count_lines) {                           // When the file is too big
                // Get the highest data row of this sheet.
                $inputFileName = $this->getAbsolutePathFromDisk($this->storage_disk) . '/' . $file_name;

                /* When the file type is known
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($this->getAbsolutePathFromDisk($this->storage_disk) . '/' . $file_name);
                */

                // When the file type is not known.
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
                $file_lines_cnt = $spreadsheet->getActiveSheet()->getHighestDataRow();


                // Save the total lines to process for all files.
                $this->c['total_lines_to_process'] = $this->c['total_lines_to_process'] + $file_lines_cnt;

            } else {                                            // Just set an estimated lines and don't really count.
                $file_lines_cnt = $this->c['total_lines_to_process'];
            }
            // Save the number of lines to process by file name.
            $this->c['file_names_all'] = [$file_name => $file_lines_cnt];

        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // PROGRESS: Write this CURRENT PROGRESS out to the progress file.
        $this->progress_output['message'] = 'Total Lines to process: ' . number_format($this->c['total_lines_to_process']);
        ProgressController::writeProgress($this->progress_file_name, $this->progress_output);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // FILE LOOP: Process the data into the tables; Loop through all of the passed file names.
        // https://docs.laravel-excel.com/3.1/imports/basics.html
        //

        foreach ($frm_file as $key => $file_name) {

            // Load the whole sheet into a collection.
            // $data = Excel::toArray($this, $file_name, $this->storage_disk);  // already counted above.
            // $this->c['file_lines_to_process'] = count($data[0]);

            ///////////////////////////////////////////////////////////////////////////////////////////
            // Get the line count by file name. (from the count lines loop above)
            $this->c['file_lines_to_process'] = $this->c['file_names_all'][$file_name];

            ///////////////////////////////////////////////////////////////////////////////////////////
            // LOG: HEADER: Log the name of the file we are now processing
            // By default this entry adds to this log for this date (daily), or creates a new one of needed.
            $log_message = config('app.nl');   // By default the 1st thing Log write is date-time+site+level
            $log_message .= 'File Name: ' . $file_name . config('app.nl');
            $log_message .= 'Line Count: ' . $this->c['file_lines_to_process'];
            Log::channel($this->log_channel)->info($log_message);


            ///////////////////////////////////////////////////////////////////////////////////////////
            // If this file $data does not test as valid, then move it to invalid and continue.
            if (!$this->checkValidFile($file_name)) {
                Log::channel($this->log_channel)->info('Invalid File: ' . $file_name);
                continue;
            }
            ///////////////////////////////////////////////////////////////////////////////////////////


            ///////////////////////////////////////////////////////////////////////////////////////////
            // Do the specific processing for the current file.
            $this->processThisFile($file_name);
            ///////////////////////////////////////////////////////////////////////////////////////////


            // Increment the files counters inside of the files loop
            $this->c['files_processed']++;
            $this->c['files_valid']++;


            // Upon successful processing of this file, move it to the processed folder.
            // But first check for duplicate names in the destination location.
            $new_filename = $this->chkDuplicateFilename($this->storage_disk, $this->processed_disk.'/'.$file_name);
            Storage::disk($this->storage_disk)->move($file_name, $this->processed_disk.'/'.$new_filename);


        } // files processing loop


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Immediately after ALL files have been processed (or moved to invalid).

        // Keep this clean for the final display (regardless of the actual final calculation; "done is done" and should display like it).
        $this->progress_output['percent'] = 100;


        // PROGRESS: Write the second to the last data out to the progress file.
        ProgressController::writeProgress($this->progress_file_name, $this->progress_output);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Build out a custom/ Final message to return to the standard progress modal (before it is closed by the end-user).
        // Note: do this before "end:" to keep from overwriting any error message that caused a skip straight to end:
        $this->progress_output['message'] =
            'Total Files: ' . $this->c['files_to_process'] . '<br/>' .
            'Valid Files: ' . $this->c['files_valid'] . '<br/>' .
            'Invalid Files: ' . $this->c['files_invalid'] . '<br/>' .
            'Total Lines: ' . number_format($this->c['total_lines_processed']) . '<br/>'.
            'Records Added: ' . number_format($this->c['records_inserted']) . '<br/>';

        if (!empty($this->c['error_message'])) {
            $this->progress_output['message'] .= 'Error: ' . $this->c['error_message'] . '<br/>';
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        end:

        sleep(1);                          // Give the client a final chance to catch up on the last progress file write
        $this->progress_output['stop_polling'] = true;    // Stop the flag to stop the polling on the client side.

        // PROGRESS: Write to the progress file one last time with all the final data and the stop_polling flag set.
        ProgressController::writeProgress($this->progress_file_name, $this->progress_output);

        // Delete this users progress file for this operation.
        // Note: This call has a reasonable time delay set (in ProgressController) to allow the client to read the results before deleting the file.
        ProgressController::deleteProgress($this->progress_file_name);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // LOG: FOOTER: Create and log this final summary message
        // Using the same one for the progress message from above.
        $log_message = config('app.nl');           // By default the 1st thing Log write is date-time+site+level
        $log_message .= str_replace('<br/>',config('app.nl'),$this->progress_output['message']);
        Log::channel($this->log_channel)->info($log_message);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Return data back to the Controller for its own use to build out any flash message.
        // Note: The standard progress js will reload the page when the end-user closes the modal.
        return $this->c;

    }



    /**
     * Check for some valid condition to exist in the file $data to determine if it's valid data or not.
     *  - Note: this will need to be implemented for each Controller that uses this trait.
     *
     * @param $data
     * @return bool
     */
    protected function checkValidFile($file_name) {

        // Check some condition on the passed $file_name and move to the specified invalid folder.
        $data = Excel::toArray($this, $file_name, $this->storage_disk);

        /*
        ///////////////////////////////////////////////////////////////////////////////////////////
        // VALID CONDITION: Check to see if this file has "valid" data in it.
        // Basically just looking at the column 16 (0 based array) in the header row to see if it matches what we expect.
        if ($data[0][1][15] != 'SERIAL NO') {

            // If this file is not valid, move it to the invalid folder.
            // But first check for duplicate names in the destination location.
            $new_filename = $this->chkDuplicateFilename($this->storage_disk, $this->invalid_disk.'/'.$file_name);
            Storage::disk($this->storage_disk)->move($file_name, $this->invalid_disk.'/'.$new_filename);


            // Remember - this implementation must increment the invalid counter every time it identifies one.
            // Increment the invalid file counter
            $this->c['files_invalid']++;


            return false;   // We did not match the defined valid condition above

        }
        */

        return true;
    }


    /**
     * Specific processing for each file.
     *  - Note: this will need to be implemented for each Controller that uses this trait.
     *
     * @param $data
     */
    //protected function processThisFile($progress_file_name, $data) {
    protected function processThisFile($file_name) {

        // For files that can be imported directly you should build the import controller
        // to use the ToModel concern and import there -- no need for processing here (see ElcmsImport2)
        // unless you have to perform more complex manipulation.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Files that need custom per file processing
        // $data = Excel::toArray($this, $file_name, $this->storage_disk);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Otherwise do it all in the Import controller and call here:
        // Excel::import($this, $file_name, $this->storage_disk);



        //$meter = new Meter;                         // Some model needed for the file processing (data inserts).

        $this->c['file_lines_processed'] = 0;       // Reset the individual file lines counter on each loop.

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Loop through each line (row) of data
        foreach ($data[0] as $row) {

            if ($this->c['file_lines_processed'] >= $this->rows_to_burn) {

                if (!empty($row[0])) {

                    ///////////////////////////////////////////////////////////////////////////////////////////
                    // THE PROCESSING WORK.

                    // 1. Does whatever processing needs to be done -- check something or whatever.

                    // 2. Populate som $insert_array with data
                    //    Use Carbon to pare dates into sql compatible formats.
                    $insert_array = [
                        'make' => ucfirst(strtolower($row[14])),
                        'serial_number' => $row[15],
                        'shipping_date' => Carbon::parse($row[3])->format('Y-m-d'),
                        'general_comments' => $row[13],
                        'site_id' => $this->new_equip_site,
                        'building_id' => $this->new_equip_bldg,
                        'disposition_code' => 'New',
                        'service_source' => 'Ricoh',

                        // 'created_by' => "system",

                        'created_at'=> date("Y-m-d h:i:sa"),
                        'created_by'=>Auth()->user()->username,
                        'updated_at'=>date("Y-m-d h:i:sa"),
                        'updated_by'=>Auth()->user()->username,
                    ];

                    ///////////////////////////////////////////////////////////////////////////////////////////
                    // Insert this record using the $f array
                    // Perform the insert

                    ///////////////////////////////////////////////////////////////////////////////////////////
                    // LOG: Create the success message for this line for the log file.
                    try {
                        $meter->fill($insert_array);
                        //$asset_financial->convertDatesToSavable($financial_array);
                        $result = $meter->save($insert_array);

                        $log_message = "sn: " . $f['dev_serialnumber'] . " - INSERTED INTO METERS; (" . $result . ")";
                        // Increment the total records added counter.
                        $this->c['records_inserted']++;

                    } catch(\PDOException $e) {
                        $log_message = "sn: " . $f['dev_serialnumber'] . " - ERROR INSERTING INTO METERS; (" . $e->getMessage() . ")";
                    }

                    ///////////////////////////////////////////////////////////////////////////////////////////
                    // LOG: Log the success or failure message for this line
                    Log::channel($this->log_channel)->info($log_message);


                    // PROGRESS:
                    // Note: only do this at the interval defined at the top of this Method. (otherwise too slow doing every single line)
                    $this->ajax_display_counter++;
                    if ($this->ajax_display_counter > $this->ajax_display_max) {

                        $this->ajax_display_counter = 0;      // Reset the display counter and

                        ///////////////////////////////////////////////////////////////////////////////////////////
                        // Calculate the percent complete (file)
                        $this->c['file_percent_complete'] = $this->c['file_lines_processed']/$this->c['file_lines_to_process'];

                        // Calculate the percent complete (total)
                        $this->c['total_percent_complete'] = $this->c['total_lines_processed']/$this->c['total_lines_to_process'];

                        // PROGRESS: Write the second to the last data out to the progress file.
                        $progress_output['percent'] = number_format($this->c['total_percent_complete']*100);
                        $progress_output['message'] = number_format($this->c['total_lines_processed'])." lines processed of ".number_format($this->c['total_lines_to_process']);
                        ProgressController::writeProgress($this->progress_file_name, $progress_output);

                    }

                }   // if this row[0] is empty

            } // if ($row_cnt > $this->rows_to_burn)


            ///////////////////////////////////////////////////////////////////////////////////////////
            // Increment the line counter inside of the rows loop
            $this->c['file_lines_processed']++;
            $this->c['total_lines_processed']++;


        } // foreach $row in this file loop

    }


    // 2 methods to turn a range of numbers into a range of excel column letters
    protected function getNameFromNumber($num) {
        $numeric = ($num - 1) % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval(($num - 1) / 26);
        if ($num2 > 0) {
            return $this->getNameFromNumber($num2) . $letter;
        } else {
            return $letter;
        }
    }
    protected function getLetterArray($start_col_no, $end_col_no) {
        $letter_array = [];
        for($cnt=$start_col_no; $cnt < $end_col_no; $cnt++) {
            array_push($letter_array, $this->getNameFromNumber($cnt));
        }
        return $letter_array;
    }



}
