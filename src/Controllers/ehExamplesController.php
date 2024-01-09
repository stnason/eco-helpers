<?php

namespace ScottNason\EcoHelpers\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use ScottNason\EcoHelpers\Classes\ehAccess;
use ScottNason\EcoHelpers\Classes\ehConfig;
use ScottNason\EcoHelpers\Classes\ehMenus;
use ScottNason\EcoHelpers\Models\ehExample;
use ScottNason\EcoHelpers\Classes\ehLayout;
use ScottNason\EcoHelpers\Classes\ehLinkbar;
use ScottNason\EcoHelpers\Models\ehPage;

class ehExamplesController extends ehBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        ehLayout::initLayout();                         // Initialize the Layout array for this page.
                                                        // And pull any page/route information found in the pages table.
        ehLayout::setAttention(false);            // Turn off the Attention message area.
        ehLayout::setDynamic(false);              // Turn off the Dynamic header message area.
        ehLayout::setOptionBlock(false);          // Turn off the Option Block area.
                                                        // Note: you can turn any of these back on to see where they appear on the page.

        $linkbar = new ehLinkbar();
        ehLayout::setLinkbar($linkbar->getLinkBar());   // Turns it on with whatever is returned from ehLinkbar;
                                                        // If empty (if you don't have permissions to any of these),
                                                        // it properly blanks it out w/o a line collapse.
        //ehLayout::setLinkbar(false);                  // Turns it off with a line collapse
        //ehLayout::setLinkbar();                       // Turns it on with defaults


        ///////////////////////////////////////////////////////////////////////////////////////////
        ehLayout::setAutoload('datatables');      // Include the datatables js and css for this page.
        ehLayout::setAutoload('datepicker');      // Include the datepicker js and css for this page.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Retrieve the layout options
        $form['layout'] = ehLayout::getLayout();

        $form['examples'] = ehExample::all();   // Load up all the records for the list.
                                                // Note: this could just as easily be some kind of restricted query to limit records.

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Define the fields to be used in the table/list view.
        $form['use_fields'] = [
            'name' => 'Name',
            'address' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'Zip Code'
        ];


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Call the view.
        return view('ecoHelpers::examples.example-list',[
            'form' => $form
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        dd('ehExamplesController@create()');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd('ehExamplesController@store()');
    }

    /**
     * Display the specified resource.
     */
    public function show(ehExample $example)
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // layout the layout options
        ehLayout::initLayout();
        ehLayout::setOptionBlock(false);

        $linkbar = new ehLinkbar();
        ehLayout::setLinkbar($linkbar->getLinkbar());

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the menu name in the dynamic header.
        if ($example->active) {
            ehLayout::setAttention('Active', 'bg-secondary');
        } else {
            ehLayout::setAttention('Not Active', 'bg-warning');
        }

 //dd(Request()->route());

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the menu name in the dynamic header.
        ehLayout::setDynamic($example->name.' ('.$example->id.')');


        ///////////////////////////////////////////////////////////////////////////////////////////
        ehLayout::setAutoload('unsaved');         // Include the form data changed check on any crud page.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set up Save, New and Delete buttons based on this user's permissions.
        ehLayout::setStandardButtons();

        /*
        // SECURITY: Manual way of doing button control.
        if (ehAccess::chkUserResourceAccess(Auth()->user(),Route::currentRouteName(),ACCESS_EDIT)) {
            ehLayout::setButton('save', '<input class="btn btn-primary" type="submit" id="save" name="save" value="Save">');
        }
        if (ehAccess::chkUserResourceAccess(Auth()->user(),Route::currentRouteName(),ACCESS_ADD)) {
            ehLayout::setButton('new', '<input class="btn btn-primary" type="submit" id="new" name="new" value="New">');
        }
        if (ehAccess::chkUserResourceAccess(Auth()->user(),Route::currentRouteName(),ACCESS_DELETE)) {
            ehLayout::setButton('delete', '<input class="btn btn-primary" type="submit" id="delete" name="delete" value="Delete">');
        }
        */


        $form['layout'] = ehLayout::getLayout();

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the form action
        $form['layout']['form_action'] = config('app.url').'/examples/'.$example->id;
        $form['layout']['form_method'] = 'PATCH';
        $form['layout']['when_adding'] = false;           // Toggles parts of the form off when adding a new record.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Cal the view.
        return view('ecoHelpers::examples.example-detail',[
            'form' => $form,
            'example' => $example
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ehExample $example)
    {
        dd('ehExamplesController@edit()');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ehExample $example)
    {

        // CRUD Router


        // Simple Validation


        // Extended Validation and Business Rules


        dd('ehExamplesController@update()');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ehExample $example)
    {
        dd('ehExamplesController@destroy()');
    }


    /**
     * Just a named route, managed by this controller, to a static view.
     *
     * @return void
     */
    public function static() {

        ehLayout::initLayout();

        $form['layout'] = ehLayout::getLayout();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Cal the view.
        return view('ecoHelpers::examples.example-static',[
            'form' => $form
        ]);

    }

}
