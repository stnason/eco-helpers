<?php

namespace ScottNason\EcoHelpers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\Kernel;

/**
 * Package Class EcoHelperServiceProvider for
 * ScottNason\EcoHelpers\EcoHelpersServiceProvider::class
 * @package ScottNason\EcoHelpers.
 *
 * Originally had to add to config/app.php; but it should now auto-discover and register itself properly.
 *
 * NOTE on the "public" paths.
 * ??? I had to register the public path in the AppServiceProvider.php
 *      for the live site in order to get the files to copy to the right place.
 *      (this may only be true when NOT USING the default Laravel public folder).
 *
 */

class EcoHelpersServiceProvider extends ServiceProvider
{

    protected $commands = [
        'ScottNason\EcoHelpers\Commands\ecoHelpersSampleData',
    ];


    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        // This section is for registering --
        //  merging into the current Laravel app - w/o copying the files --
        // (like the routes file, disk definition, some of the core views amd the eco-constants.)

        // Note: in the boot() method below we will actually publish (copy) files for end user use.

        // Core security and permissions constants.
        $this->mergeConfigFrom(__DIR__.'/config/eco-constants.php', 'eco-helpers');


        // Package Routes file.
        // (? Laravel documentation shows this under the boot() section.)
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');


        // Use this syntax in views to access non-published (package) views:
        // ecoHelpers::folder.view-name
        // (? Laravel documentation shows this under the boot() section.)
        $this->loadViewsFrom(__DIR__.'/views', 'ecoHelpers');


        // Merge in a disk definition into the application's filesystems config file.
        // For eco-helpers.contact_photo_disk name (default: users)
        app()->config["filesystems.disks.users"] = [
            'driver' => 'local',
            'root' => storage_path('app/users'),
            'url' => env('APP_URL').'/images/users',
            //'visibility' => 'public',
            'throw' => false,
        ];


        // Publish middleware?
        // Add the Global middleware.
        // $kernel->pushMiddleware(ehCheckPermissions::class);  // $kernel only exists below in the boot() method. ??
        app('router')->aliasMiddleware('check_permissions', \ScottNason\EcoHelpers\Middleware\ehCheckPermissions::class);


    }

    /**
     * Bootstrap services.
     *  - Register all publishable resources.
     *  - (define which files come from which local path and go to which user project folder)
     *
     * @return void
     */
    public function boot(Kernel $kernel)
    {
        ///////////////////////////////////////////////////////////////////////////////////////////
        // How to publish resources in the host project:
        // php artisan vendor:publish --provider="ScottNason\eco-helpers\EcoHelpersServiceProvider"
        // php artisan vendor:publish (will give you a list of packages with publishable assets to choose from)


        //TODO: if you run the ui:auth command a second time it warns you that folders already exist.
        // How are they doing that (and then use that in here.)

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Publish the end-user editable eco-helpers config file.
        // Note: We're specifically excluding the eco-constants.php file (it's being registered above).
        $this->publishes([
            __DIR__.'/config/eco-helpers.php' => config_path('eco-helpers.php'),
        ], 'config');

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Publish the end-user editable views.
        $this->publishes([
            __DIR__.'/views/publishable/views-ecoHelpers' => resource_path('views/ecoHelpers'),         // The main views
            __DIR__.'/views/publishable/views-auth' => resource_path('views/auth-ecoHelpers'),          // This will need to be renamed to just "auth" on the other side.          // The auth views
            __DIR__.'/views/publishable/views-auto-load' => resource_path('views/ecoHelpers/auto-load'),    // The auto-loader views
            ], 'views');


        /* TODO: publish the final end-user auth views
        ?? can we rename the current views/auth folder if it exists ??
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Publish the end-user editable views.
        $this->publishes([
            __DIR__.'/views/auth' => resource_path('views/auth'),
        ], 'views');
        */


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Publish the css, js, image and auto-loader resources.
        $this->publishes([
            __DIR__.'/public-publishable/vendor-ecoHelpers-css' => public_path('vendor/ecoHelpers/css'),
            __DIR__.'/public-publishable/vendor-ecoHelpers-js' => public_path('vendor/ecoHelpers/js'),
            __DIR__.'/public-publishable/vendor-ecoHelpers-images' => public_path('vendor/ecoHelpers/images'),
            ], 'public');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Publish or Register (?) the database migrations.
        // This copies the migrations to the parent app's migrations folder rather than just "using" them from the package
        /*
        $this->publishes([
            __DIR__.'/database/migrations' => database_path('/migrations'),
        ], 'migrations');
        */

        // This registers them for use without the need to copy them.
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Publish the Classes-publishable resources.
        // register()   - "To make available to the app w/o copying the files."
        // boot()       - "Copy files to the laravel app so the developer has access to them."

        // Note: Had to go with the camel-cased "ecoHelpers" for the target folders
        //       since "eco-helpers" appeared to be an invalid
        //       namespace name.

        // This will depend on whether the drop-down menu creation--and the auto-loaders check--stays
        // in the Layout class and is passed to the $layout array -- or -- moved to an @inject
        // so the template can handle it.

        // The verdict was to keep ehLayout in the package. (rather than publish a user accessible Layout class)
        // (but hanging onto this code as a reminder until the final testing.)
        /*
        $this->publishes([
            __DIR__.'/Classes-publishable/Layout.php' => app_path('Classes/ecoHelpers'),
        ], 'eco-helpers-classes');
        */

        // This is designed to be immediately extended and modified by the end-users.
        $this->publishes([
            __DIR__.'/Classes-publishable/ValidList.php' => app_path('Classes/ValidList.php'),
        ], 'classes');


        /* TODO: I think the final decision was to "modify" (the extends and the public $casts)
            the original user file rather than publishing a new one.
            !!! BUT: there may be value in doing this and adding all the needed $labels !!!
        // Publish any models?
        // So far, there is only 1 model to publish. The others are all kept in the package for now.
            __DIR__.'/Models-publishable/User.php' => app_path('Models/ecoHelpers/User.php'),
        ], 'models');
        */

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Publish the controllers.
        //  So far, I don't know how to force (or ask) an overwrite from here --
        //  So I opted for appending "-ecoHelpers" to the end of the Auth directory (for now)
        //  and having that be a manual edit (like the views) after the fact.
        //  === To implement, you will have to delete the originals and rename these. ===
        $this->publishes([

            //Laravel UI Auth only:
            //__DIR__.'/Controllers-publishable/Auth/LoginController.php' => app_path('Http/Controllers/Auth/ecoHelpers_LoginController.php'),


            // Laravel Breeze Auth:
            // (copy all of the "modified" Breeze controllers)
            __DIR__.'/Controllers-publishable/Auth' => app_path('Http/Controllers/Auth-ecoHelpers'),


            // Sample OOTB Home Controller:
            __DIR__.'/Controllers-publishable/ehHomeController.php' => app_path('Http/Controllers/ehHomeController.php'),

        ], 'controllers');



        ///////////////////////////////////////////////////////////////////////////////////////////
        //TODO:
        // photos to publish for the sample user (ehAdmin and ehUser)??
        // (Note: the storage disk is defined above in the register() method)
        // Sample data is executed by the console command registered below.



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Register the package's artisan console commands.
        // Right now it's just the one to create the sample data.
        //  (see the protected $commands variable at the top.)
        //  Note: Because of how potentially dangerous this command could be on Live data,
        //       I opted not to do this automatically with a post install script
        //       -> composer.json -> post-install-cmd.
        //  Note: That w/o registering, you get:
        //       ERROR  There are no commands defined in the "eco-helpers" namespace.

        //  This only works when all the namespacing is exactly correct. (see the command $signature)
        $this->commands($this->commands);


    }
}
