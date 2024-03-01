<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use ScottNason\EcoHelpers\Controllers\ehBaseController;
use ScottNason\EcoHelpers\Classes\ehLayout;
use ScottNason\EcoHelpers\Classes\ehConfig;


/**
 * Welcome to the eco-helpers framework and utilities.
 * This is the initial splash page and HomeController example.
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
         * use true/false to set that area to display or not.
         * use 'text' to set what you want in that area.
         * Check out the documentation for more information.
         *
        ehLayout::initLayout();             // Initialize the Layout array for this page.
        ehLayout::setBanner()
        ehLayout::setName()
        ehLayout::setIcon()
        ehLayout::setDescription()
        ehLayout::setLinkbar()
        ehLayout::setDynamic()
        ehLayout::setFlash()
        ehLayout::setAttention()
        ehLayout::setOptionBlock()
        ehLayout::setButton()
        ehLayout::setAll()
        $variable = ehLayout::getLayout();  // Retrieve the values for this layout and pass them to the view
        */


        ehLayout::initLayout();                        // Initialize the Layout array for this page.
                                                       // And pull any page/route information found in the pages table.
        ehLayout::setName('Eco-Helpers Home Page');
                                                       // If there is no entry in the pages table then you'll have to specify the page title/name here.
        ehLayout::setAttention(true);            // Turn off the Attention message area.
        ehLayout::setDynamic(true);              // Turn off the Dynamic header message area.
        ehLayout::setOptionBlock(true);          // Turn off the Option Block area.
        // Note: you can turn any of the defined layout areas on or off using true or false.

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Override the system banner set in the system configuration and settings page.
        // Pull the ecohelper's version and last update from the configuration system
        // and use those values to create and populate the system banner:
        ehLayout::setBanner('Controller overriding system banner ('.ehConfig::get('APP_VER').', '.ehConfig::get('APP_LASTUPDATE').')');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Retrieve the layout options and package them up for the view.
        $form['layout'] = ehLayout::getLayout();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Call the view and pass the 'layout' to it.
        return view('ecoHelpers::eco-welcome', [
            'form' => $form
        ]);

    }



}