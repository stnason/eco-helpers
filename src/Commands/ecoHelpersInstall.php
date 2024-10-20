<?php

namespace ScottNason\EcoHelpers\Commands;

use Illuminate\Console\Command;
use File;


//TODO: would be helpful to have a --Parameter to specify which item to process by itself. (Default would be --all)
// Check on the par conversion program for a possible idea of how to implement.

/**
 * Published as the artisan command 'eco-helpers:install' this command
 * is responsible for copying over files and folders that may need to be overwritten from the originals.
 * NOTE: See the populateOperationsArray() at the bottom for detail on the operations called for in this script.
 *
 */
class ecoHelpersInstall extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'eco-helpers:install';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Complete the installation of the User model and authentication for the eco-helpers package.';

    /**
     * Did the user select Rename on any of the questions?
     * @var bool
     */
    protected bool $did_rename = false;

    /**
     * If the user selected Rename on any of the choices then show this message at the end of the install script.
     * @var string
     */
    protected string $rename_message = "Remember to review your code and copy out and delete the renamed items.";


    protected string  $user_aborted = '';   // Contains the message for aborting at any point in time.

    /**
     * Set up the array of the operations to be performed in this script file.
     * Note: these parameters will be passed to
     *  $this->>replaceOrOverwrite($copy_to, $rename_to, $copy_from)
     *
     * @var array|array[]
     */
    protected array $operations_array = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {

        // Display the onscreen script warning message.
        system('clear');
        $this->showOnScreenWarning();

        // Ask the user if they want to continue. Final chance to punch out.
        if ($this->confirm('Do you still want to continue?', false)) {


            // Get the operations to perform in the R/O/S/A loop.
            $this->operations_array = $this->populateOperationsArray();

            $last_key = count($this->operations_array);

            // Note: replaceOrOverwrite($copy_to, $rename_to, $copy_from)
            // Do the work here.

            foreach ($this->operations_array as $key=>$operation) {

                if ($key === $last_key) { // $last_key should be sample routes and needs additional processing below.
                    break;
                }
                if (empty($operation['copy_to']) || empty($operation['rename_to']) || empty($operation['copy_from'])) {
                    dd('Oops. Missing something. ('.$key.')');
                }
                $this->line(sprintf('%02d', $key) . '-' . $operation['name']);
                $exit = $this->replaceOrOverwrite(
                    $operation['copy_to'],
                    $operation['rename_to'],
                    $operation['copy_from']
                );
                $this->newLine(2);
                // Did the user request an Abort?
                if (!empty($exit)) {
                    $this->user_aborted = $exit;
                    goto stop_execution;
                }
            }

/*
            ////////////////////////////////////////////////////////////////////////////////////////////
            // User.php
            $this->replaceOrOverwrite(
                app_path('Models/User.php'),
                app_path('Models/User-original.php'),
                __DIR__.'/../Models-publishable/User.php'
            );
            $this->newLine(2);

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Auth Views (folder)
            $this->replaceOrOverwrite(
                base_path('resources/views/auth'),
                base_path('resources/views/auth-original'),
                __DIR__.'/../views/publishable/auth'
            );
            $this->newLine(2);

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Auth Controller (folder)
            $this->replaceOrOverwrite(
                app_path('Http/Controllers/Auth'),
                app_path('Http/Controllers/Auth-original'),
                __DIR__.'/../Controllers-publishable/Auth'
            );
            $this->newLine(2);

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Requests (folder) [LoginRequest.php]
            $this->replaceOrOverwrite(
                app_path('Http/Requests'),
                app_path('Http/Requests-original'),
                __DIR__.'/../Requests-publishable'
            );
            $this->newLine(2);
*/

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Sample Routes
            // Append the sample routes to the web.php file here. (with a question of course).
            if ($this->confirm('Do you want to add the eco-helpers sample routes to the web.php routes file?',false)) {
                $routes_file = fopen(base_path('routes/web.php'), "a") or die("Unable to open app routes file!");
                $append_this = "///////////////////////////////////////////////////////////////////////////////////////////\n";
                fwrite($routes_file, $append_this);
                $append_this = "// For initial eco-helpers testing and integration.\n";
                fwrite($routes_file, $append_this);
                $append_this = "require __DIR__.'/../vendor/scott-nason/eco-helpers/src/routes/eco-sample-routes.php';\n";
                fwrite($routes_file, $append_this);
                fclose($routes_file);
                $this->line('Sample routes have been appended to the routes/web.php');
            }


        } else {
            ////////////////////////////////////////////////////////////////////////////////////////////
            // User aborted -- safely punch out before doing anything.
            $this->user_aborted = "User aborted procedure before making any changes.";

        }


        stop_execution:

        $this->newLine(2);
        // If anything was renamed then show that alert message.
        if ($this->did_rename) {
            $this->info($this->rename_message);
        }
        $this->newLine(1);

        if (!empty($this->user_aborted)) {
            ////////////////////////////////////////////////////////////////////////////////////////////
            // User aborted -- punched out at some point in time.
            $this->line("    Installation interrupted.");
            $this->info("    ".$this->user_aborted);

        } else {
            ////////////////////////////////////////////////////////////////////////////////////////////
            // Completed all operations and finished normally.
            $this->line("    Install Completed.");
        }


        $this->newLine(2);
    }

    /**
     * Build out and display the script's startup, on-screen
     * warning message explaining what's about to happen.
     *
     * (separating this here to keep the main body of the code cleaner)
     * @return void
     */
    protected function showOnScreenWarning() {

        $this->newLine(1);
        $this->line( '#################################################################');
        $this->error('                        !!!!! WARNING !!!!!                      ');
        $this->line( '#################################################################');


        $this->info("
This procedure is about to overwrite some of the app's original 
 files and add others.
    ");

        $this->line("
Unless there's a really good reason for doing this, it's 
recommended to only run this on a fresh, clean, new
Laravel app installation.
    ");

        //TODO: change this to loop the array for what's happening.
        $this->info("
This will include:

01 - Replacing the app's User.php
02 - Replacing the app's Http/Controllers/Auth folder.
03 - Replacing the app's Requests/Auth/LoginRequest file.
04 - Replacing the apps' views/auth folder.
05 - Copying the js, css, font and image assets.
06 - Copying all of the autoloader js and css files.
07 - Copying a clean copy of the config/eco-helpers.php config file.
08 - Copying the base, views/ecoHelpers page area templates.
09 - Copying the views/ecoHelpers/examples templates.
10 - Copying the extendable views/ecoHelpers/admin templates.
11 - Copying a clean copy of the config/version.php file.
12 - Add the eco-helpers sample/example routes to your web.php file.

    ");

        $this->info("
At each step in the process you'll have the opportunity to
[R]ename, [O]verwrite or [S]kip that file or folder.
    ");

        $this->line('
If this is not a fresh clean Laravel install, you may
 want to consider canceling this operation now.
');
        $this->newLine(1);

        $this->error('!!!       PLEASE DO NOT RUN THIS ON A PRODUCTION INSTANCE     !!!');
        $this->error('!!!            WHERE YOU ALREADY HAVE A WORKING APP           !!!');
    }


    /**
     * Automate the copy from/ to operations and associated prompts.
     *
     * @param $copy_to
     * @param $rename_to
     * @param $copy_from
     * @return string|null
     */
    protected function replaceOrOverwrite($copy_to, $rename_to, $copy_from) {

        //$this->line($copy_to);
        //$this->line($rename_to);

        // We need to use a different copy command if we're copying a directory so check here first.
        $is_dir = false;
        if (is_dir($copy_from)) {
            $is_dir = true;
        }

        // Check to see if the file already exists in the destination or not.
        if (file_exists($copy_to)) {

            $this->line($copy_to." already exists.");
            $answer = $this->ask('[R]ename, [O]verwrite, [S]kip it or [A]bort?', 's');

            // User selected Rename.
            if ( strtoupper($answer) == 'R') {
                // Set the Rename flag for the final display message.
                $this->did_rename = true;

                // Rename the original file to the $rename_to
                rename($copy_to, $rename_to);
                $this->info('Contents of '.$copy_to.' renamed to '.$rename_to);

                // Then to the copy
                if ($is_dir) {
                    // Is a directory so use:
                    File::copyDirectory($copy_from, $copy_to);
                } else {
                    // Is not a directory so just use copy().
                    File::copy($copy_from, $copy_to);
                }
                $this->info('Contents of '.$copy_from.' have been copied to '.$copy_to);
            }

            // User selected Overwrite.
            if ( strtoupper($answer) == 'O') {
                // Overwrite the original User.php
                // Then to the copy
                if ($is_dir) {
                    // Is a directory so use:
                    File::copyDirectory($copy_from, $copy_to);
                } else {
                    // Is not a directory so just use copy().
                    File::copy($copy_from, $copy_to);
                }
                $this->info('Contents of '.$copy_from.' have been copied to '.$copy_to);
            }

            // User selected Skip.
            if ( strtoupper($answer) == 'S') {
                // For now we do nothing but exit this function and return.
                return null;
            }

            // User selected aborted.
            if ( strtoupper($answer) == 'A') {
                // Exit this function and return an abort message for the calling program to use..
                return "User aborted processing.";
            }

        } else {
            $this->line($copy_to." was not found.");
            // So just do the copy and display the result
            // Then to the copy
            if ($is_dir) {
                // Is a directory so use:
                File::copyDirectory($copy_from, $copy_to);
            } else {
                // Is not a directory so just use copy().
                File::copy($copy_from, $copy_to);
            }
            $this->info('Contents of '.$copy_from.' have been copied to '.$copy_to);

        }

        return null;
    }



    protected function populateOperationsArray() {
        return [
            1 => [
                "name"=>"Replace User.php",
                "description"=>"Replacing the app's User.php",
                "copy_to"=>app_path('Models/User.php'),
                "rename_to"=>app_path('Models/User-original.php'),
                "copy_from"=>__DIR__.'/../Models-publishable/User.php',
            ],
            2 => [
                "name"=>"Replace Auth Controllers",
                "description"=>"Replacing the app's Http/Controllers/Auth folder.",
                "copy_to"=>app_path('Http/Controllers/Auth'),
                "rename_to"=>app_path('Http/Controllers/Auth-original'),
                "copy_from"=>__DIR__.'/../Controllers-publishable/Auth',
            ],
            3 => [
                "name"=>"Replace LoginRequest.php",
                "description"=>"Replacing the app's Requests/Auth/LoginRequest file.",
                "copy_to"=> app_path('Http/Requests'),
                "rename_to"=>app_path('Http/Requests-original'),
                "copy_from"=> __DIR__.'/../Requests-publishable',
            ],
            4 => [
                "name"=>"Replacing auth views",
                "description"=>"Replacing the apps' views/auth folder.",
                "copy_to"=>base_path('resources/views/auth'),
                "rename_to"=>base_path('resources/views/auth-original'),
                "copy_from"=> __DIR__.'/../views/publishable/auth',
            ],
            5 => [
                "name"=>"JS assets",
                "description"=>"Copying the js file assets.",
                "copy_to"=>public_path('vendor/ecoHelpers/js'),
                "rename_to"=>public_path('vendor/ecoHelpers/js-original'),
                "copy_from"=>__DIR__.'/../public-publishable/vendor-ecoHelpers-js',
            ],
            6 => [
                "name"=>"CSS assets",
                "description"=>"Copying the css file assets.",
                "copy_to"=>public_path('vendor/ecoHelpers/css'),
                "rename_to"=>public_path('vendor/ecoHelpers/css-original'),
                "copy_from"=>__DIR__.'/../public-publishable/vendor-ecoHelpers-css',
            ],
            7 => [
                "name"=>"Image assets",
                "description"=>"Copying the image file assets.",
                "copy_to"=>public_path('vendor/ecoHelpers/images'),
                "rename_to"=>public_path('vendor/ecoHelpers/images-original'),
                "copy_from"=>__DIR__.'/../public-publishable/vendor-ecoHelpers-images',
            ],
            8 => [
                "name"=>"Font assets",
                "description"=>"Copying the font file assets.",
                "copy_to"=>base_path('storage/app/fonts'),
                "rename_to"=>base_path('storage/app/fonts-original'),
                "copy_from"=>__DIR__.'/../storage-publishable/app/fonts',
            ],

            9 => [
                "name"=>"ecoHelpers page area templates",
                "description"=>"Copying the base, views/ecoHelpers page area templates.",
                "copy_to"=>base_path('resources/views/ecoHelpers'),
                "rename_to"=>base_path('resources/views/ecoHelpers-original'),
                "copy_from"=>__DIR__.'/../views/publishable/ecoHelpers',
            ],

            /* Dead-end -- there is no value in this without having stubbed in version of the controllers and models too!!
            10 => [
                "name"=>"Core Admin templates",
                "description"=>"Copying the extendable views/ecoHelpers/admin templates.",
                "copy_to"=>base_path('resources/views/ecoHelpers/admin'),
                "rename_to"=>base_path('resources/views/ecoHelpers/admin-original'),
                "copy_from"=>__DIR__.'/../views/publishable/admin',
            ],
            */

            10 => [
                "name"=>"Autoloader files.",
                "description"=>"Copying all of the js and css autoload files.",
                "copy_to"=>base_path('resources/views/ecoHelpers/autoload'),
                "rename_to"=>base_path('resources/views/ecoHelpers/autoload-original'),
                "copy_from"=>__DIR__.'/../views/publishable/autoload',
            ],

            11 => [
                "name"=>"Examples templates",
                "description"=>"Copying the views/ecoHelpers/examples templates.",
                "copy_to"=>base_path('resources/views/ecoHelpers/examples'),
                "rename_to"=>base_path('resources/views/ecoHelpers/examples-original'),
                "copy_from"=>__DIR__.'/../views/publishable/examples',
            ],

            12 => [
                "name"=>"Default eco-helpers.php config file",
                "description"=>"Copying a clean copy of the config/eco-helpers.php config file.",
                "copy_to"=>base_path('config/eco-helpers.php'),
                "rename_to"=>base_path('config/eco-helpers-original.php'),
                "copy_from"=>__DIR__.'/../config/eco-helpers.php',
            ],

            13 => [
                "name"=>"Clean version.php file.",
                "description"=>"Copying a clean copy of the config/version.php file.",
                "copy_to"=>base_path('config/eco-helpers.php'),
                "rename_to"=>base_path('config/eco-helpers-original.php'),
                "copy_from"=>__DIR__.'/../config/eco-helpers.php',
            ],

            14 => [         // No need to define - the last $key will terminate the loop and move on to this routine.
                "name"=>"Example Routes",
                "description"=>"",
                "copy_to"=>"",
                "rename_to"=>"",
                "copy_from"=>"",
            ],

        ];
    }




}
