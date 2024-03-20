<?php

namespace ScottNason\EcoHelpers\Classes;

use Illuminate\Support\Facades\DB;

/**
 * A set of utility functions for working with CSV files.
 *
 */

class ehCSV
{

    /**
     * Replaces the PHP explode function with one that looks for a defined list of delimiters
     * Uses a defined a list of valid delimiters for any csv style data list
     * @param $csvList
     * @return array
     */
    public static function multi_explode($csvList) {

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Remove any spaces in the input data -- this could cause issues with individual array values.
        $csvList = str_replace(' ', '', $csvList);

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Define a standard character to use for the final explode below
        // Doesn't really matter here but, lets use something other than a comma (just because of Excel export issues)
        $standardChar = ':';

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Define the valid delimiter types allowed in any csv "style" list
        $validDelimiterList = array(
            ',',
            ':',
            ';',
            '&'
        );

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Normalize the input data by replacing any valid character with a standard character
        foreach($validDelimiterList as $char) {
            $csvList = str_replace($char, $standardChar, $csvList);
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Remove any double or triple delimiter entries. (doing this after normalizing the delimiter)
        $csvList = str_replace($standardChar.$standardChar, $standardChar, $csvList);
        $csvList = str_replace($standardChar.$standardChar.$standardChar, $standardChar, $csvList);

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Remove any delimiter as either the first or last character
        $csvList = rtrim($csvList,$standardChar);
        $csvList = ltrim($csvList,$standardChar);

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Then do the explode on the normalized data; making sure to check for a single entry too
        if (strpos($csvList,$standardChar)>0) {
            $delimitedArray = explode($standardChar,$csvList);
        } else {
            $delimitedArray[0] = $csvList;  // make sure and return a single entry if no delimiter found
            if ($delimitedArray[0]=='') {
                $delimitedArray = [];
            }
        }


        /* Example of extended custom processing for unique situations.
           This was from the original eesfm site and provided additional information for Remote sites.
        ////////////////////////////////////////////////////////////////////////////////////////////
        // Check to see if this list contains the keyword "remote" for remote site support.
        // In that case this person gets all the buildings set up as remote (include non-active but no archived)
        $delimitedArray = array_map('strtolower', $delimitedArray);     // Convert any text to lower case.
        if (in_array('remote',$delimitedArray)) {

            // We're in here because we saw the keyword "Remote" entered in this array (building list)

            // Remove the "remote" keyword from the array
            unset($delimitedArray[array_search('remote',$delimitedArray)]);
            // Then this is seems to be needed after unsetting a value to get it to re-use that key number again.
            $delimitedArray = array_values($delimitedArray);

            // A Remote site building query then add the bIDs to the $delimitedArray created above.
            $q="
SELECT
    bID
FROM
    buildings
LEFT JOIN sites ON (sID = bSiteAssignedTo)
WHERE
    bArchived <> 1
    #AND bActive = 1    # For now, using just Not-Archived buildings (including non-active)
    AND sArchived <> 1
    #AND sActive = 1    # For now, using just Not-Archived sites (including non-active)
    AND sSupport_Level LIKE 'Remote%'
";
            $r = DB::select($q);
            // Then add to the end of the $delimitedArray
            if ($r) {
                foreach($r as $row) {
                    $delimitedArray[] = $row->bID;
                }
            }

        }
        */

        // Return an indexed array containing the exploded csv style list items
        return $delimitedArray;
    }


    /**
     * Convert a $resultset array to csv format for download.
     * Original code from https://gist.github.com/albofish/c496bfa9183556155a18
     *      (but could never get the object to work with the returned $resultset so no chunking).
     *
     *  NOTE: When creating a file for attachment set download = false
     *      (remember there's a $name_p2 to null out if you're not including it)
     *      WARNING: This assumes you have an "storage/app/tmp" folder to write to.
     *
     *      This function creates the download file but DOES NOT DELETE IT. You'll have to deal with that.
     *
     * @param $columns      - column name array to use in the exported file
     * @param $resultset    - data set to include in the file
     * @param $name_p1      - Exported filename will be {$name_p1}_{$name_p2}_yyy-mm-dd.csv
     * @param $name_p2
     * @param $download     - true to force a download from the browser; false to create a local copy.
     */
    public static function outputCSV($columns, $resultset, $name_p1, $name_p2 = null, $download = true)
    {

        // If the $resultset is null or completely empty at lease initialize a valid array so the foreach doesn't crash.
        if (empty($resultset)) {
            $resultset = [];
        }

        // If no $name_p2 then leave out the leading underscore for it.
        if ($name_p2) {
            $filename = $name_p1."_".$name_p2;              // Add the 2 parts of the file name together.
        } else {
            $filename = $name_p1;                           // Just use the first part of the file name.
        }
        $filename .= "_".date("Y-m-d") . ".csv";    // Add today's data and the .csv extension.


        if ($download) {
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            $output = fopen("php://output", "w");
        } else {
            //$output = fopen(sys_get_temp_dir().'/'.$filename, "w");   // Docs say that system temp deletes file after you close it.
            $filename = base_path().'/storage/app/tmp/'.$filename;      // Using our own pre-defined tmp Laravel tmp folder in storage
            $output = fopen($filename, "w");
        }

        fputcsv($output, $columns);

        foreach ($resultset as $row) {

            // Initiate a temporary array to hold the data from our $use_fields list fields.
            $write_row = [];

            foreach ($columns as $include_field) {          // Loop the use fields list and check each $row=>$field against it.
                foreach ($row as $field => $value) {        // Inefficient, but the only way I can seem to get the $row data in order of the $use_fields list.
                    if ($include_field == $field) {
                        $write_row[$field] = $value;        // If the $row=>$field matches the $use_fields field then grab this data into our array.
                    }
                }
            }
            fputcsv($output, $write_row);
        }


        // Just return the path to the file
        if (!$download) {
            return $filename;
        }

        fclose($output);

    }


    /**
     * Create a temporary table in the tmp disk Storage.
     * Used mainly by the cron jobs to create a temp file for the email attachment.
     *
     * @param $table_name
     * @param int $chunk
     * @param string $id
     * @return string
     */
    public static function createCSV_fromTable($table_name, $chunk = 500, $id = 'id', $headers = true) {

        $filename = self::getFilename($table_name,'csv');

        $filename = base_path().'/storage/app/tmp/'.$filename;      // Using our own pre-defined tmp Laravel tmp folder in storage
        $output = fopen($filename, "w");

        // pull headers from the table
        if ($headers) {
            fputcsv($output, \Schema::getColumnListing($table_name));
        }

        //$resultset = [];
        DB::table($table_name)
            ->orderBy($id)
            ->take(500)
            ->chunk($chunk, function($rows) use (& $output) {
                foreach ($rows as $row) {
                    //$resultset[] = json_decode(json_encode($row), true);    // Each row is an array.
                    fputcsv($output, json_decode(json_encode($row), true));
                    //$resultset[] = $row;    // each row is a stdClass object
                }
            });

        fclose($output);
        return $filename;
    }




    /**
     * Used by bulkdelete(); bulkkxfer()
     * @param $csvList
     * @return array
     */
    public static function prepare_csv_list($csvList) {
        // 1. read in the list into an internal array;
        $list = self::parse_csv($csvList);

        // 2. check to see if there's an empty line at the end
        if ($list[count($list)-1][0]=='') {
            unset($list[count($list)-1]);   // if so, remove it from array
        }
        return $list;
    }

    /**
     * Used by bulkdelete(); bulkkxfer()
     * Returns a two-dimensional array or rows and fields
     * Note; str_getcsv does not deal with Rows of data properly - a single row csv list
     * @param $csv_string
     * @param string $delimiter
     * @param bool $skip_empty_lines
     * @param bool $trim_fields
     * @return array
     */

    public static function parse_csv ($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
    {
        $enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string);
        $enc = preg_replace_callback(
            '/"(.*?)"/s',
            function ($field) {
                return urlencode(utf8_encode($field[1]));
            },
            $enc
        );
        $lines = preg_split($skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);
        return array_map(
            function ($line) use ($delimiter, $trim_fields) {
                $fields = $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
                return array_map(
                    function ($field) {
                        return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
                    },
                    $fields
                );
            },
            $lines
        );
    }


    /**
     * Create a standard file naming format for the export
     *  date + site type + table_name (or can override and pass another name than the table_name)
     *
     * @param $name
     * @return string
     */
    public static function getFilename($name, $extension = 'csv') {
        return date('Y-m-d').'_'.config('app.env').'_'.ucfirst($name).'.'.$extension;
    }


    /**
     * Just remove empty lines from the input.
     *
     * @param $input
     * @return string|string[]|null
     */
    public static function removeBlankLines($input) {
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n", $input);
    }

}