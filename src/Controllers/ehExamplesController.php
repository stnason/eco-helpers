<?php

namespace ScottNason\EcoHelpers\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use ScottNason\EcoHelpers\Models\ehExample;
use ScottNason\EcoHelpers\Classes\ehLayout;
use ScottNason\EcoHelpers\Classes\ehLinkbar;


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
                                                        // If it returns empty (if you don't have permissions to any of these),
                                                        // then it properly blanks it out w/o a line collapse.
        //ehLayout::setLinkbar(false);                  // Turns the Linkbar off with a line collapse
        //ehLayout::setLinkbar();                       // Turns the Linkbar on with defaults


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
        //TODO: incorporate Archived into the Dynamic header somehow.
        if ($example->active) {
            ehLayout::setAttention('Active', 'bg-secondary');
        } else {
            ehLayout::setAttention('Not Active', 'bg-warning');
        }



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the menu name in the dynamic header.
        ehLayout::setDynamic($example->name.' ('.$example->id.')');


        ///////////////////////////////////////////////////////////////////////////////////////////
        ehLayout::setAutoload('unsaved');         // Include the form data changed check on any crud page.
        ehLayout::setAutoload('datepicker');      // Include the datepicker js and css for this page.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set up Save, New and Delete buttons based on this user's permissions to this route.
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
        $form['layout']['when_adding'] = false;           // May toggles parts of the form off when adding a new record.


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
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Edit is not implemented in eco-helpers.
        // The show() screen allows editing and adding new records for those that have permissions.
        // Note: see the CRUD Router in the @update() method below.

        dd('ehExamplesController@edit()');

    }

    /**
     * Update the Example record.
     */
    public function update(Request $request, ehExample $example)
    {

        // Crud Router - new
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Watch for a 'new' button submit then redirect to the create method.
        // which, in turn, will change the form method for the new record submit.
        if ($request->has('new')) {
            return redirect(route('examples.create'));
        }

        // Standard (simple) Laravel Validation
        ///////////////////////////////////////////////////////////////////////////////////////////
        /*
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        */

        // Extended Validation with Business (data consistency) Rules
        ///////////////////////////////////////////////////////////////////////////////////////////
        // When you need more control over how this update() operation interacts with other fields.
        $request = $this->dataConsistencyCheck($request, $example);


        // Actual update to the examples table.
        ///////////////////////////////////////////////////////////////////////////////////////////
        $result = $example->update($request->input());


        // Return flash message
        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say that in the flash message.
        if ($result) {
            session()->flash('message','Example record for <strong>'.$example->name.'</strong> saved successfully. ');
        } else {
            session()->flash('message','Something went wrong.');
        }


        // Go back to the examples detail page we were on.
        ///////////////////////////////////////////////////////////////////////////////////////////
        return redirect(route('examples.show',[$example->id]));


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ehExample $example)
    {

        // Any specific "delete" Business Rules

        dd('ehExamplesController@destroy()');
    }


    /**
     * Just a named route, managed by this controller, to a static view.
     *
     * @return void
     */
    public function staticPage() {

        ehLayout::initLayout();

        $form['layout'] = ehLayout::getLayout();

        $form['some_variable'] = '';    // passed from the controller

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Cal the view.
        return view('ecoHelpers::examples.example-static',[
            'form' => $form
        ]);

    }


    /**
     * Extended validation, custom error messages and business rules.
     * Use this when there are other data fields that interact with each other.
     * (as in , if you change one field, something else needs to change...)
     *
     * @param $request
     * @param $example
     * @return mixed
     */
    protected function dataConsistencyCheck($request, $example) {

        // If you want to include the simple validation, you can do that here.
        ///////////////////////////////////////////////////////////////////////////////////////////
        $request->validate([
            'name' => 'required',
            'email' => 'required',
        ]);


        // BUSINESS RULES:
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Check for any situation that may require other fields to stay in sync or
        // change to a particular value.


        // RULE 1. If example user is set to archived then make then inactive too.
        //          Archived users cannot be 'active'.
        if ($request->archived) {
            // Then change the value for active in the $request
            $request->merge(['active'=>0]);
        }

        /*
        // RULE 2. Check for some condition.
        if ($checkSomething) {
            // Change the value in the $request
            $request->merge(['value'=>'new_value']);
        }

        // RULE 3. Check for some condition.
        if ($checkSomething) {
            // Change the value in the $request
            $request->merge(['value'=>'new_value']);
        }
        */

        return $request;

    }



}
