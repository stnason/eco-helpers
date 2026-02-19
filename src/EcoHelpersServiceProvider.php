<?php

namespace ScottNason\EcoHelpers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\Kernel;

/**
 * Package Class EcoHelperServiceProvider for
 * ScottNason\EcoHelpers\EcoHelpersServiceProvider::class.
 *
 * No need to add to config/app.php manually anymore.
 * Laravel now auto-discovers and registers it properly.
 *
 *  register()   - "To make available to the app w/o copying any files."
 *  boot()       - "Copy files to the laravel app so the app developer can modify them."
 *
 * NOTE: On the "public" path.
 *       Only when using a public path other than the default Laravel app/public:
 *       I had to register the public path in the AppServiceProvider.php
 *       in order to get the public files to copy to the right place.
 *       (this seems to only be needed when NOT USING the default Laravel public folder)
 *
 * Note: Had to go with the camel-cased "ecoHelpers" for the target folders
 *       since "eco-helpers" appears to be an invalid namespace name.
 *
 * @package ScottNason\EcoHelpers.
 */

class EcoHelpersServiceProvider extends ServiceProvider
{

    protected $commands = [
        'ScottNason\EcoHelpers\Commands\ecoHelpersSampleData',
        'ScottNason\EcoHelpers\Commands\ecoHelpersInstall',
    ];


    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        /*
         * REGISTER
         * This section "registers" the package which basically "merges" these settings
         * into the current Laravel app - W/O copying any files.
         * (like the routes file, disk definition, some of the core views amd the eco-constants.)
         *
         * Note: in the boot() method below we will actually publish (copy) files for application developer's use.
         */

        ///////////////////////////////////////////////////////////////////////////////////////////
        // CONSTANTS
        // The core eco-helpers security and permissions constants.
        // These are used by all security permissions checks.
        $this->mergeConfigFrom(__DIR__.'/config/eco-constants.php', 'eco-helpers');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // ROUTES
        // The core eco-helpers package routes file.
        // This provides access to the core package functions like roles, menus/pages, users.
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // VIEWS
        // Provide access to the eco-helpers core package views
        // Note on proper syntax when calling any of these views from within the app:
        //      ecoHelpers::folder.view-name
        $this->loadViewsFrom(__DIR__.'/views', 'ecoHelpers');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // FILESYSTEM
        // Merge this disk definition into the application's filesystems config file.
        // This is for eco-helpers.contact_photo_disk name (default: users)
        app()->config["filesystems.disks.users"] = [
            'driver' => 'local',
            'root' => storage_path('app/users'),
            'url' => env('APP_URL').'/images/users',
            'throw' => false,
        ];
        // The temp path for the captcha image creation.
        app()->config["filesystems.disks.temp"] = [
            'driver' => 'local',
            'root' => storage_path('app/temp'),
        ];
        // The path for the captcha font file(s).
        app()->config["filesystems.disks.fonts"] = [
            'driver' => 'local',
            'root' => storage_path('app/fonts'),
        ];


        ///////////////////////////////////////////////////////////////////////////////////////////
        // MIDDLEWARE
        // Add the Global middleware.
        // $kernel->pushMiddleware(ehCheckPermissions::class);  // $kernel only exists below in the boot() method.
        // This is the syntax that works for "registering" this middleware for use in the app.
        app('router')->aliasMiddleware('check_permissions', \ScottNason\EcoHelpers\Middleware\ehCheckPermissions::class);

    }

    /**
     * Bootstrap services.
     *  - Register all publishable resources.
     *  -   Define which files come from which package path and go to which application path.
     *
     * @return void
     */
    public function boot(Kernel $kernel)
    {

        /*
         * PUBLISH
         * This section publishes resources (copies them) to the host project.
         * Usage:
         *  php artisan vendor:publish --provider="ScottNason\eco-helpers\EcoHelpersServiceProvider"
         *      - or simply:
         *  php artisan vendor:publish
         *      - will provide a list of vendor packages with publishable assets to choose from.
         */

        ///////////////////////////////////////////////////////////////////////////////////////////
        // CONFIG
        // Publish the application developer editable eco-helpers config file.
        // Note: We're specifically excluding the eco-constants.php file.
        //       (it's been registered above already)
        $this->publishes([
            __DIR__.'/config/eco-helpers.php' => config_path('eco-helpers.php'),
            __DIR__.'/config/version.php' => config_path('version.php'),
        ], 'config');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // VIEWS
        // Publish the application developer editable views.
        $this->publishes([
            __DIR__.'/views/publishable/ecoHelpers' => resource_path('views/ecoHelpers'),             // The main page area views
            __DIR__.'/views/publishable/ecoHelpers/_layouts' => resource_path('views/ecoHelpers/_layouts'),      // The main page include areas and partials
            __DIR__.'/views/publishable/ecoHelpers/admin' => resource_path('views/ecoHelpers/admin'),            // Extendable core admin page views
            __DIR__.'/views/publishable/ecoHelpers/auth' => resource_path('views/ecoHelpers/auth'),              // The authentication views
            __DIR__.'/views/publishable/ecoHelpers/autoload' => resource_path('views/ecoHelpers/autoload'),      // The autoloader views
            __DIR__.'/views/publishable/ecoHelpers/examples' => resource_path('views/ecoHelpers/examples'),      // The example views


            // Moving this to an artisan executable "eco-helpers:install" command
            // __DIR__.'/views/publishable/views-auth' => resource_path('views/auth-ecoHelpers'),

            ], 'views');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // ASSETS
        // Publish the css, js, image and autoloader resources (public vendor assets).
        $this->publishes([
            __DIR__.'/public-publishable/vendor-ecoHelpers-css' => public_path('vendor/ecoHelpers/css'),
            __DIR__.'/public-publishable/vendor-ecoHelpers-js' => public_path('vendor/ecoHelpers/js'),
            __DIR__.'/public-publishable/vendor-ecoHelpers-images' => public_path('vendor/ecoHelpers/images'),
            ], 'public');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // MIGRATIONS
        // Note: this section can Publish or Register database migrations.
        //
        /* This copies the migrations to the parent app's migrations folder.
        $this->publishes([
            __DIR__.'/database/migrations' => database_path('/migrations'),
        ], 'migrations');
        */

        // This simply "registers" them for use without copying them out to the app's migration folder.
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // CLASSES
        // These are the classes that are designed to be modified by the application developer.
        $this->publishes([
            __DIR__.'/Classes-publishable/ValidList.php' => app_path('Classes/ValidList.php'),
        ], 'classes');


        ///////////////////////////////////////////////////////////////////////////////////////////
        /* Can't overwrite the original User model here so moving this to an executable artisan install command.
        // Publish any models?
        // So far, there is only 1 model to publish. The others are all kept in the package for now.
            __DIR__.'/Models-publishable/User.php' => app_path('Models/ecoHelpers/User.php'),
        ], 'models');
        */

        ///////////////////////////////////////////////////////////////////////////////////////////
        // CONTROLLERS
        // Publish the application developer modifiable eco-helpers controllers.
        $this->publishes([

            //Laravel UI Auth only:
            //__DIR__.'/Controllers-publishable/Auth/LoginController.php' => app_path('Http/Controllers/Auth/ecoHelpers_LoginController.php'),

            // Laravel Breeze Auth:
            // (copy all of the "modified" Breeze controllers)
            // Moving this to an executable artisan install command.
            // __DIR__.'/Controllers-publishable/Auth' => app_path('Http/Controllers/Auth-ecoHelpers'),

            // Sample OOTB Home Controller:
            __DIR__.'/Controllers-publishable/ehHomeController.php' => app_path('Http/Controllers/ehHomeController.php'),

        ], 'controllers');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // PHOTOS
        //TODO:
        // photos to publish for the sample user (ehAdmin and ehUser)??
        // (Note: the storage disk is defined above in the register() method)
        // Sample data is executed by the console command registered below.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // COMMANDS
        // Register the eco-helpers artisan console commands.
        //
        //  (see the protected $commands variable at the top.)
        //  Note: Because of how potentially dangerous this command could be on Live data,
        //       I opted not to do this automatically with a post install script
        //       -> composer.json -> post-install-cmd.
        //  Note: That w/o registering, you get:
        //       ERROR  There are no commands defined in the "eco-helpers" namespace.
        //  THIS ONLY WORKS when all the namespacing is EXACTLY correct.
        //      (see the command protected $signature in the Commands\commandName file)
        $this->commands($this->commands);

    }
}
