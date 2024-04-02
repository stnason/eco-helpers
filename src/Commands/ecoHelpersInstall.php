<?php

namespace ScottNason\EcoHelpers\Commands;

use Illuminate\Console\Command;
use File;

/**
 * Published as the artisan command 'eco-helpers:sample-data' to call the ehSampleData class
 * that's responsible for building out the initial (required) system data and examples.
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
     * Execute the console command.
     */
    public function handle()
    {

        // Display the onscreen script warning message.
        system('clear');
        $this->showOnScreenWarning();

        // Ask the user if they want to continue. Final chance to punch out.
        if ($this->confirm('Do you still want to continue?', false)) {


            // Might be nice to have these options too (but maybe too confusing/ dangerous to have in this command with User copy?)
            // Maybe this can be a separate "overwrite" command or something like that.
            // Do you want ot overwrite the original vendor published Config file?
            // Do you want ot overwrite the original vendor published Views?
            // Do you want ot overwrite the original vendor published Auto-Load files?
            // Do you want to overwrite the original vendor public assets (css, js, images) [individually?)


            // Do the work here.
            $this->replaceOrOverwrite(
                app_path('Models/User.php'),
                app_path('Models/User-original.php'),
                __DIR__.'/../Models-publishable/User.php'
            );
            $this->newLine(2);

            $this->replaceOrOverwrite(
                base_path('resources/views/auth'),
                base_path('resources/views/auth-original'),
                __DIR__.'/../views/publishable/views-auth'
            );
            $this->newLine(2);

            $this->replaceOrOverwrite(
                app_path('Http/Controllers/Auth'),
                app_path('Http/Controllers/Auth-original'),
                __DIR__.'/../Controllers-publishable/Auth'
            );
            $this->newLine(2);

            /*
            ////////////////////////////////////////////////////////////////////////////////////////////
            // User.php model
            // If User.php exists then ask what you want to do?
            if (file_exists(app_path('Models/User.php'))) {
                $this->line('** User.php model already exists **');
                $answer = $this->ask('[R]ename or [O]verwrite it?', 'r');

                if ( strtoupper($answer) == 'R') {
                    // Rename the original User.php to User-original.php
                    //rename($from, $to);
                    //copy($from, $to);
                    $this->info(' - User.php renamed to User-original.php.');
                }
                if ( strtoupper($answer) == 'O') {
                    // Rename the original User.php to User-original.php
                    //copy($from, $to);
                    $this->info(' - User.php copied over original User.php.');
                }
            }
            $this->newLine(1);
            */

        } else {

            ////////////////////////////////////////////////////////////////////////////////////////////
            // User aborted -- safely punch out before doing anything.
            $this->info("User aborted procedure. No changes made at this time.");

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
        $this->line( '#####################################################');
        $this->error('                 !!!!! WARNING !!!!!                 ');
        $this->line( '#####################################################');

        $this->info("
This procedure is about to overwrite the original 
 User.php file and all of the Breeze views and controllers.
 (you'll be able to choose either or both)
    ");

        $this->line('
If this is not a fresh clean Laravel install, you may
 want to consider canceling this now.
');
        $this->newLine(1);
        $this->error('!! DO NOT RUN THIS ON A PRODUCTION INSTANCE 
 WHERE YOU ALREADY HAVE A WORKING APP !!');
    }



    protected function replaceOrOverwrite($full_filename, $rename_to, $copy_from) {

        //$this->line($full_filename);
        //$this->line($rename_to);

        // Need a different copy command if we're copying a directory so check here.
        $is_dir = false;
        if (is_dir($copy_from)) {
            $is_dir = true;
        }

        // Check to see if the file already exists in the destination or not.
        if (file_exists($full_filename)) {

            $this->line($full_filename." already exists.");
            $answer = $this->ask('[R]ename or [O]verwrite it?', 'r');

            if ( strtoupper($answer) == 'R') {
                // Rename the original file to the $rename_to
                rename($full_filename, $rename_to);
                $this->info('Contents of '.$full_filename.' renamed to '.$rename_to);

                // Then to the copy
                if ($is_dir) {
                    // Is a directory so use:
                    File::copyDirectory($copy_from, $full_filename);
                } else {
                    // Is not a directory so just use copy().
                    File::copy($copy_from, $full_filename);
                }
                $this->info('Contents of '.$copy_from.' have been copied to '.$full_filename);
            }
            if ( strtoupper($answer) == 'O') {
                // Overwrite the original User.php
                // Then to the copy
                if ($is_dir) {
                    // Is a directory so use:
                    File::copyDirectory($copy_from, $full_filename);
                } else {
                    // Is not a directory so just use copy().
                    File::copy($copy_from, $full_filename);
                }
                $this->info('Contents of '.$copy_from.' have been copied to '.$full_filename);
            }

        } else {
            $this->line($full_filename." was not found.");
            // So just do the copy and display the result
            // Then to the copy
            if ($is_dir) {
                // Is a directory so use:
                File::copyDirectory($copy_from, $full_filename);
            } else {
                // Is not a directory so just use copy().
                File::copy($copy_from, $full_filename);
            }
            $this->info('Contents of '.$copy_from.' have been copied to '.$full_filename);

        }

    }

}
