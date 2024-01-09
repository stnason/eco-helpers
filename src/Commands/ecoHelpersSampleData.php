<?php

namespace ScottNason\EcoHelpers\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use ScottNason\EcoHelpers\Classes\ehSampleData;

class ecoHelpersSampleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eco-helpers:sample-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample data for the ecoHelpers framework.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        // Display the onscreen script warning message.
        $this->showOnScreenWarning();

        // Ask the user if they want to continue. Final chance to punch out.
        if ($this->confirm('Do you really want to continue?', false)) {

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Execute the code to build out the sample data here.
            $this->info('Setting up the initial sample data now:' . "\n");

           $this->info(ehSampleData::createSampleData());

        } else {

            ////////////////////////////////////////////////////////////////////////////////////////////
            // User aborted -- safely punch out w/o doing anything.
            $this->info("User aborted procedure. No changes made at this time.");

        }

        $this->newLine(2);

    }


    /**
     * Build out and display the script's startup, on-screen
     * warning message explaining what's about to happen.
     * (just separating this down here to keep the main
     *  body of the code a little cleaner)
     *
     * @return void
     */
    protected function showOnScreenWarning() {

        $this->newLine(1);
        $this->line( '#####################################################');
        $this->error('                 !!!!! WARNING !!!!!                 ');
        $this->line( '#####################################################');

        $this->info('
This procedure will COMPLETELY WIPE OUT and replace
 these tables with NEW DATA:

    - eh_examples
    - eh_roles
    - eh_roles_lookup
    - eh_pages
    - eh_access_tokens
    - eh_notifications
    - eh_settings
    ');

        $this->line('Additionally -- the users ehAdmin and ehUser 
 WILL BE DELETED and recreated.');
        $this->newLine(1);
        $this->error('!! DO NOT RUN THIS ON A PRODUCTION INSTANCE 
 WHERE YOU NEED TO KEEP ANY OF THIS DATA !!');
    }


}
