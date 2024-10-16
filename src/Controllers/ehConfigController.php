<?php

namespace ScottNason\EcoHelpers\Controllers;

use ScottNason\EcoHelpers\Models\ehSetting;
use ScottNason\EcoHelpers\Classes\ehLayout;
use ScottNason\EcoHelpers\Classes\ehAccess;
use ScottNason\EcoHelpers\Classes\ehLinkbar;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/**
 * The Controller responsible for managing the crud interaction with the system settings in the eh_settings table.
 *
 */
class ehConfigController extends ehBaseController
{
    /**
     * The view for the SettingsController@show.
     * Provided here to allow overriding if you need a custom view.
     *
     * @var string
     */
    protected $view_show = 'ecoHelpers::admin.setting-detail';




    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // There should be only 1 site settings record.
        // So just redirect to that id #.
        return redirect('/config/1');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {

        // Mote: not passing the ehSetting $setting model so we can check to see if it's not found and deal with that here.

        // Do we have a settings table entry?
        // If not, create the first time run trough defaults.
        if (!ehSetting::find(1)) {
            return redirect('settings/create');
        } else {
            $setting = ehSetting::find(1);          // Load up the first (only) settings table record.
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Reset the display areas to defaults and pull the page info from Pages
        ehLayout::initLayout();

        ehLayout::setDynamic(false);
        ehLayout::setOptionBlock(false);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // If the site is locked then set the Attention Message on this page to indicate that.
//        if ($setting['site_lockout']) {
//            ehLayout::setAttention('Site is currently locked out!');
//        } else {
            // If it's not locked then just clear the Attention message and make sure it's turned off.
            ehLayout::setAttention(false);

//        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Create the form buttons -- if you have access
        if (ehAccess::chkUserResourceAccess(Auth()->user(),Route::currentRouteName(),ACCESS_EDIT)) {
            ehLayout::setButton('save', '<input class="btn btn-primary" type="submit" id="save" name="save" value="Save">');
        } else {
            ehLayout::setButton(false);
        }
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set any needed auto loaders for this page
        //ehLayout::setAutoload('textedit');            // Include the TextEditor plugin.
        ehLayout::setAutoload('datepicker');      // Include the datepicker plugin.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Build out the Linkbar for this page (or module)
        $linkbar = new ehLinkbar;
        // This is a case where the table name is different than the route used.
        $linkbar->setExportTableName('eh_settings');
        //$linkbar->setHideExportAll(false);
        ehLayout::setLinkbar($linkbar->getLinkBar());     // Turns it on with whatever is returned from ehLinkbar;
                                                          // If empty (if you don't have permissions to any of these),
                                                          // it properly blanks it out w/o a line collapse.
        //ehLayout::setLinkbar(false);                    // turns it off with a line collapse
        //ehLayout::setLinkbar();                         // turns it on with defaults



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Get the page array so we can add to it as needed.
        $form['layout'] = ehLayout::getLayout();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the form action (submit to itself)
        if (empty($setting->id)) {
            // First time through - no records in table yet so force an add/insert.
            $form['layout']['form_action'] = config('app.url').'/config/create';
            $form['layout']['form_method'] = 'get';
        } else {
            // Record 1 already exists so just use a normal update.
            $form['layout']['form_action'] = config('app.url').'/config/'.$setting->id;
            $form['layout']['form_method'] = 'patch';
        }


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Return the primary Site Settings display/ edit view
        return view($this->view_show,[
            'form' => $form,
            'setting' => $setting
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        echo 'got here. edit';
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ehSetting $config)
    {

        // Data validation and any auto-set updates.
        $request = $this->dataConsistencyCheck($request);


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Save/Update the site setting (using mass assignment)
        $result = $config->update($request->input());


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the Flash message.
        // If all went okay then just say so.
        if ($result) {
            session()->flash('message','<strong>Site Settings</strong> saved successfully.');
        } else {
            session()->flash('message','Something went wrong. '.$result);
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // NOTE: if you just redirect to /config here the index() method redirects
        // to show() and effectively kills the flash message.
        return redirect('/config/1');

    }



    public function store(Request $request, ehSetting $setting) {
        // There is only one record and you'll never create another one.
    }



    protected function dataConsistencyCheck(Request $request) {

        // Stubbed out here in case anything in settings needs
        // to have a data validation and consistency check.

        return $request;
    }


}
