<?php

namespace ScottNason\EcoHelpers\Commands;

use Illuminate\Console\Command;

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
        $this->showOnScreenWarning();

        // Ask the user if they want to continue. Final chance to punch out.
        if ($this->confirm('Do you still want to continue?', false)) {

            // Do the work here.

            // Might be nice to have these options too (but maybe too confusing/ dangerous to have in this command with User copy?)
            // Maybe this can be a separate "overwrite" command or something like that.
            // Do you want ot overwrite the original vendor published Config file?
            // Do you want ot overwrite the original vendor published Views?
            // Do you want ot overwrite the original vendor published Auto-Load files?
            // Do you want to overwrite the original vendor public assets (css, js, images) [individually?)


            ////////////////////////////////////////////////////////////////////////////////////////////
            // Ask if you want to replace the original User.php model with the package version?
            if ($this->confirm('Do you want to replace the original User.php file with the one form the package?', false)) {

                // Display what we're doing now.
                $this->newLine(1);
                $this->info('User model copied.');

                //$path = app_path();
                //$path = app_path('Models/User.php');

                // If User.php exists then delete it. (or rename it for now?)
                if (file_exists(app_path('Models/User.php'))) {
                    $this->info('Current User model exists.');
                }

                // Copy the User.php file from this package

            }


            ////////////////////////////////////////////////////////////////////////////////////////////
            // Ask if you want to replace all of the original Breeze views awith the package versions.
            if ($this->confirm('Do you want to replace the Breeze Views with the one from the package?', false)) {

                // Display what we're doing now.
                $this->newLine(1);
                $this->info('Breeze Views overwritten.');


                // if folder exist "views\auth" then delete it
                $path = base_path('resources/views');
                if (!file_exists( $path ) || !is_dir( $path)) {
                    $this->info('Current Breeze views folder exists.');
                }

                // Copy the auth views
            }


            ////////////////////////////////////////////////////////////////////////////////////////////
            // Ask if you want to replace all of the original Breeze controller with the package versions.
            if ($this->confirm('Do you want to replace the Breeze Controllers with the one from the package?', false)) {

                // Display what we're doing now.
                $this->newLine(1);
                $this->info('Breeze Controllers overwritten.');

                // if folder exists "controllers\auth" then delete it
                $path = app_path('Http/Controllers/Auth');
                if (!file_exists( $path ) || !is_dir( $path)) {
                    $this->info('Current Breeze Controllers/Auth folder exists.');
                }


                // Copy auth/controller files
            }


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

        $this->info('
This procedure is about to overwrite the original User.php file
 and all of the Breeze views and controllers.
    ');

        $this->line('
If this is not a fresh clean Laravel install, you want
 to considering canceling this now.
');
        $this->newLine(1);
        $this->error('!! DO NOT RUN THIS ON A PRODUCTION INSTANCE 
 WHERE YOU ALREADY HAVE A WORKING APP !!');
    }

}
