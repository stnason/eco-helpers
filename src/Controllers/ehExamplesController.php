<?php

namespace ScottNason\EcoHelpers\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use ScottNason\EcoHelpers\Models\ehExample;
use ScottNason\EcoHelpers\Classes\ehLayout;
use ScottNason\EcoHelpers\Classes\ehLinkbar;
use ScottNason\EcoHelpers\Classes\ehAccess;


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
        // Define the fields to be used in the Datatables/list view.
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
     * Show the form for creating a new example resource.
     *
     */
    public function create(ehExample $example)
    {
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Create an empty model to use for this "add" form.
        // Looks like we can type hint above [create(Model $model)],
        // Or use this that may be a little more verbose.
        // $example = new ehExample();

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Initialize and set the screen display options.
        ehLayout::initLayout();
        ehLayout::setOptionBlock(false);
        ehLayout::setDynamic('Adding New Example');

        $linkbar = new ehLinkbar();
        ehLayout::setLinkbar($linkbar->getLinkbar());

        // SECURITY: Button control.
        if (ehAccess::chkUserResourceAccess(Auth()->user(),Route::currentRouteName(),ACCESS_EDIT)) {
            ehLayout::setButton('save', '<input class="btn btn-primary" type="submit" id="save" name="save" value="Save">');
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Retrieve the Layout data for the view.
        $form['layout'] = ehLayout::getLayout();

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the form action
        $form['layout']['form_action'] = route('examples.store');   // The name of the resourceful route.
        $form['layout']['form_method'] = 'POST';                    // Set the create() form method.

        // Note: ehLayout sets this to a default value of false; so only need to set it when creating a new record.
        $form['layout']['when_adding'] = true;                      // May be used to turn parts of the show()
                                                                    // form off when adding a new record.

        ///////////////////////////////////////////////////////////////////////////////////////////
        return view('ecoHelpers::examples.example-detail',[
            'form' => $form,
            'example'=>$example
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // Create a new empty record. Then fill it with the input().
        $example = new ehExample();
        $example->fill($request->input());

        // Standard (simple) Laravel Validation (specific to update)
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


        // Extended Validation with Business (data consistency) Rules (shared with update/ store)
        ///////////////////////////////////////////////////////////////////////////////////////////
        // When you need more control over how this update() operation interacts with other fields.
        $request = $this->dataConsistencyCheck($request, $example);


        // Actual save to the examples table.
        ///////////////////////////////////////////////////////////////////////////////////////////
        $result = $example->save($request->input());


        // Return flash message
        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say that in the flash message.
        if ($result) {
            session()->flash('message','Example record for <strong>'.$example->name.'</strong> added successfully. ');
        } else {
            session()->flash('message','Something went wrong.');
        }


        // Go back to the examples detail page with the new id.
        ///////////////////////////////////////////////////////////////////////////////////////////
        return redirect(route('examples.show',[$example->id]));

    }

    /**
     * Display the specified resource.
     */
    public function show(ehExample $example)
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Initialize and set the screen display options.
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
        if ($example->archived) {
            ehLayout::setAttention('Archived (Not Active)', 'bg-warning');
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the menu name in the dynamic header.
        ehLayout::setDynamic($example->name.' ('.$example->id.')');


        ///////////////////////////////////////////////////////////////////////////////////////////
        ehLayout::setAutoload('unsaved');         // Include the form data changed check on any crud page.
        ehLayout::setAutoload('datepicker');      // Include the datepicker js and css for this page.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Automatic button control.
        // User will get "Save", "New" and/or "Delete" buttons based on their acting_role permissions on this route (page).
        ehLayout::setStandardButtons();

        /*
        // SECURITY: Manual way of doing individual button access control.
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


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Retrieve the layout data for the view.
        $form['layout'] = ehLayout::getLayout();

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the form action
        $form['layout']['form_action'] = route('examples.update',[$example->id]);
        $form['layout']['form_method'] = 'PATCH';           // Set the update() form method.

        // Just a reminder: ehLayout sets this to an initial value of false.
        // Only need to set this to true in create() when the blade template uses it to drop sections
        // out when adding a new record.
        // $form['layout']['when_adding'] = false;          // May be used to turn parts of the show()
                                                            // form off when adding a new record.

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

        // Standard (simple) Laravel Validation (specific to update)
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

        // Extended Validation with Business (data consistency) Rules (shared with update/ store)
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

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Any specific "delete" BUSINESS RULES:

        // 1. RULE: Let's say you're not allowed to delete an active user.
        ///////////////////////////////////////////////////////////////////////////////////////////
        if ($example->active) {
            session()->flash('message',"You're <strong>not allowed</strong> to delete a currently <strong>Active</strong> user.");
            return redirect(route('examples.show',[$example->id]));
        }



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Do the actual deletion.
        $result = $example->delete();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say that in the flash message.
        if ($result) {
            session()->flash('message','Example record for <strong>'.$example->name.'</strong> deleted successfully. ');
        } else {
            session()->flash('message','Something went wrong.');
        }

        // Go back to the examples list page.
        ///////////////////////////////////////////////////////////////////////////////////////////
        return redirect(route('examples.index'));
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
        // Call the view.
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

        ///////////////////////////////////////////////////////////////////////////////////////////
        // WHEN ADDING: Skip any business rules you don't want to run when adding a new record.
        // (If there is no id, then this should be a new record.)
        if (!empty($example->id)) {

            // BUSINESS RULES (skip when adding):
            ///////////////////////////////////////////////////////////////////////////////////////////
            // Place any business rules here that will be SKIPPED when adding a new record.
            // (they will be run when updating.)


        }


        // BUSINESS RULES (for both adding and update):
        // Place any business rules here that will RUN when both adding AND updating a record.
        ///////////////////////////////////////////////////////////////////////////////////////////


        // RULE 1. If example user is set to archived then make them inactive too.
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
