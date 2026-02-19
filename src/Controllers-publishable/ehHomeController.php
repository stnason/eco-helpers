<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use ScottNason\EcoHelpers\Controllers\ehBaseController;
use ScottNason\EcoHelpers\Classes\ehLayout;
use ScottNason\EcoHelpers\Classes\ehConfig;
use ScottNason\EcoHelpers\Classes\ehLinkbar;


/**
 * A sample home controller with some basic formatting page setup examples and information.
 *
 */
class ehHomeController extends ehBaseController
{

    /**
     * This should be an open page with no auth requirements.
     * Use web so we have access to session and $errors.
     */
    public function __construct()
    {
        $this->middleware('web');
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        /*
         * Each ehLayout::set command corresponds to a defined area on the base template.
         *  use true/false to set that area to display or not.
         *  Calling with just () is the same as ('true')
         *  use ('some text') to set what you want to display in that area.
         *  (Keeping in mind that page name, icon and description will come from the Menus/Pages entries
         *   automatically and banner should just use what's set in Setting Settings.)
         *
         * Check out the documentation for more information.
         *
        ehLayout::initLayout();             // Initialize the Layout array for this page.
        ehLayout::setBanner()               // You can turn it off or on but normally will allow system setting to control the content.

        ehLayout::setTitle()                // ehLayout will attempt to pull this from the eh_pages table (Menus/Pages entry)
        ehLayout::setIcon()                 // ehLayout will attempt to pull this from the eh_pages table (Menus/Pages entry)
        ehLayout::setDescription()          // ehLayout will attempt to pull this from the eh_pages table (Menus/Pages entry)

        ehLayout::setLinkbar()              // Can be manually generated or called without parameters to create a set a links for this module.

        ehLayout::setDynamic()              // Generally set by the Controller to show something specific to the record being displayed.
        ehLayout::setAttention()            // Generally set by the Controller to show something specific to the record being displayed.
        ehLayout::setOptionBlock()          // Generally set by the Controller to show something specific to the record being displayed.

        ehLayout::setButton()               // Can generate Save, New, Delete buttons for the form based on the user's active role permissions.

        ehLayout::setFlash()                // Mostly for consistency and convenience but the flash message area can be set or disabled when required for special cases.
        ehLayout::setAll()                  // Quickly turn on/off (true/false) all display areas.

        // Standard usage to pass the layout to the view
        // All of the core eco-helpers views are expecting the $form['layout'] array.
        $form['layout'] = ehLayout::getLayout();
        */


        ///////////////////////////////////////////////////////////////////////////////////////////
        // You must initialize the layout before you can do anything with it.
        // This creates an internal array and attempts to populate from the eh_pages table.
        ehLayout::initLayout();                 // Initialize the Layout array for this page.
                                                // And pull any page/route information found in the pages table.

        ehLayout::setAll();                     // Turn all of the display areas on.

        ///////////////////////////////////////////////////////////////////////////////////////////
        // You can override the system banner set in the system settings page.
        // NORMALLY YOU WOULD NOT DO THIS. THIS IS JUST AN EXAMPLE OF HOW YOU "CAN" IF YOU NEED TO.
        // We're just pulling the eco-helper's version and last update from the configuration system
        //  and using those values to create and populate the system banner:
        // Comment this out to have the banner pull from the system settings entry.
        ehLayout::setBanner('Controller overriding system banner ('.ehConfig::get('eh-app-version').', '.ehConfig::get('eh-last-update').')');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // If there is no entry in the pages table then you'll have to specify the page name (title), description and icon here.
        ehLayout::setTitle('Page Name');                    // Page name/ title
        ehLayout::setDescription('page description');       // Text for what appears under the page name (title).
        ehLayout::setIcon("fa-solid fa-leaf");              // A Font Awesome icon class (w/o the <i> tag)


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Controller controlled, record specific messages.
        // Try entering different text inside each one and seeing the result on the home page.
        ehLayout::setAttention(true);            // Turn off the Attention message area.
        //ehLayout::setAttention('');                  // w/o being defined this will pull the default form the eco-helpers config file.
        ehLayout::setDynamic(true);              // Turn off the Dynamic header message area.
        //ehLayout::setDynamic('');                    // w/o being defined this will pull the default form the eco-helpers config file.
        ehLayout::setOptionBlock(true);          // Turn off the Option Block area.
        //ehLayout::setOptionBlock('');                // w/o being defined this will pull the default form the eco-helpers config file.

        // Using the pre-defined variable for the "image not available" photo in the UserFunctions trait.
        $src = 'data:' . 'jpg' . ';base64,'.User::$na_image_data;
        ehLayout::setOptionBlock('<img src="'.$src.'" alt="option block">');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Create a Linkbar for this page (or module)
        // This will autogenerate a set of links from this page's parent's module.
        // Since this is the Home Page and has no parent module, we'll leave it undefined and
        // let it pull the defaults from the eco-helpers config file.
        //
        // $linkbar = new ehLinkbar;
        // ehLayout::setLinkbar($linkbar->getLinkBar());
        //
        // Or turn it off:
        // ehLayout::setLinkbar(false);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Then, before calling the view, you have to retrieve the layout
        //  and package it up for use in the view.
        $form['layout'] = ehLayout::getLayout();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Call the view and pass the 'layout' to it.
        return view('ecoHelpers.eco-welcome', [
            'form' => $form
        ]);

    }



}