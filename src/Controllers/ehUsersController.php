<?php

namespace ScottNason\EcoHelpers\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


use ScottNason\EcoHelpers\Classes\ehConfig;
use ScottNason\EcoHelpers\Classes\ehLayout;
use ScottNason\EcoHelpers\Classes\ehLinkbar;
use ScottNason\EcoHelpers\Models\ehUser;
use ScottNason\EcoHelpers\Models\ehRole;
use ScottNason\EcoHelpers\Classes\ehAccess;
use ScottNason\EcoHelpers\Rules\CheckEmails;


class ehUsersController extends ehBaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // Instead of a list -- just go to the logged in person's record.
        // (Of course this assumes that you have to be logged in to even access this features so there is no error checking on Auth()).

        // You could just as easily implement a list here (see ehExamplesController@index().

        if (Auth()->guest()) {
            return redirect('/users/' . ehUser::first()->id);
        } else {
            return redirect('/users/' . Auth()->user()->id);
        }

    }


    public function create(User $user)
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
        ehLayout::setDynamic('Adding New User');

        $linkbar = new ehLinkbar();
        ehLayout::setLinkbar($linkbar->getLinkbar());

        // SECURITY: Button control.
        if (ehAccess::chkUserResourceAccess(Auth()->user(), Route::currentRouteName(), ACCESS_EDIT)) {
            ehLayout::setButton('save', '<input class="btn btn-primary" type="submit" id="save" name="save" value="Save">');
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Retrieve the Layout data for the view.
        $form['layout'] = ehLayout::getLayout();

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the form action
        $form['layout']['form_action'] = route('users.store');      // The name of the resourceful route.
        $form['layout']['form_method'] = 'POST';                    // Set the create() form method.

        // Note: ehLayout sets this to a default value of false; so only need to set it when creating a new record.
        $form['layout']['when_adding'] = true;                      // May be used to turn parts of the show()
        // form off when adding a new record.

        ///////////////////////////////////////////////////////////////////////////////////////////
        return view('ecoHelpers::users.user-detail', [
            'form' => $form,
            'user' => $user
        ]);

    }

    public function store(Request $request)
    {

        // Create a new empty record. Then fill it with the input().
        $user = new User();
        $user->fill($request->input());

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
        $request = $this->dataConsistencyCheck($request, $user);


        // Actual save to the users table.
        ///////////////////////////////////////////////////////////////////////////////////////////
        $result = $user->save($request->input());


        // Return flash message
        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say that in the flash message.
        if ($result) {
            session()->flash('message', 'User record for <strong>'.$user->first_name .' '.$user->last_name.' </strong> added successfully. ');
        } else {
            session()->flash('message', 'Something went wrong.');
        }


        // Go back to the users detail page with the new id.
        ///////////////////////////////////////////////////////////////////////////////////////////
        return redirect(route('users.show', [$user->id]));


    }

    public function show(Request $request, ehUser $user)
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Reset the display areas to defaults and pull the page info from Pages
        ehLayout::initLayout();

        //ehLayout::setOptionBlock(false);

        $linkbar = new ehLinkbar();
        ehLayout::setLinkbar($linkbar->getLinkbar());


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Build the Dynamic header information.
        ehLayout::setDynamic($user->fullName());


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Build the Attention message.
        if ($user->archived || !$user->login_active) {
            ehLayout::setAttention('User - not Active.');
        } else {
            ehLayout::setAttention('Active User', 'bg-secondary');
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Setup the User's image (or no image file)
        ehLayout::setOptionBlock('<img alt="' . $user->id . '" title="' . $user->fullName() . '" src="' . ehUser::getUserPhoto($user->id) . '">');


        ///////////////////////////////////////////////////////////////////////////////////////////
        ehLayout::setAutoload('unsaved');         // Include the form data changed check on any crud page.

        // SECURITY: Button control.
        ehLayout::setStandardButtons();


        /*
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
        //$form['layout']['form_action'] = config('app.url').'/users/'.$user->id;
        $form['layout']['form_action'] = route('users.update', [$user->id]);     // Set the form submit action.
        $form['layout']['form_method'] = 'PATCH';                               // Set the update() form method.


        // Get all the groups that I'm a member of:
        $form['my_roles'] = $user->getUserRoles($user->id);


        // Set the radio button for the default_role
        // The template used the 'my_roles' array to loop through and decide which one is checked.
        return view('ecoHelpers::users.user-detail', [
            'form' => $form,
            'user' => $user,
            //'this_page' => $this->form['pageList']
        ]);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request             $request
     * @param \ScottNason\EcoHelpers\Models\ehUser $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ehUser $user)
    {

        // Crud Router - new
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Watch for a 'new' button submit then redirect to create which will change the form method for the next submit
        if ($request->has('new')) {
            return redirect(route('users.create'));
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Data Validation and consistency check.
        $request = $this->dataConsistencyCheck($request, $user);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Update the roles table.
        // Was a role added from the drop down?
        // Then go ahead and add that to the Roles lookup table.
        if ($request->role_id) {
            $user->addUserRole($user->id, $request->role_id);
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // PASSWORD CHANGE
        // Have we changed the password through the Admin User Profile?
        // If so, then make sure we hash it.
        // Note that if you haven't actually entered a password, the $request (form) password
        // will be the same hashed password stored in the database ($user->password).
        if ($request->password != $user->password) {

            // Hash the newly entered password
            $request->merge(['password' => Hash::make($request->password)]);
        } else {

            // If password is the same, then just take it out of the $request and leave it alone.
            // (or you'll re-hash it and make it unusable!)
            $request->request->remove('password');
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Update the users table.
        $result = $user->update($request->input());


        // If the default role radio button is checked; then set the default_role to that value.
        //  Remember that RolesLookupController@destroy() will null out the user's default_role if we remove that group from them.
        if ($request->default_role_group != null) {
            $user->default_role = $request->default_role_group;
            $user->save();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say so to the flash message.
        if ($result) {
            session()->flash('message', 'User profile for <strong>' . $user->fullName() . '</strong> updated successfully. ');
        } else {
            session()->flash('message', 'Something went wrong.');
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Go back to the detail page.
        return redirect('/users/' . $user->id);

    }

    public function destroy()
    {

        dd('ehUsersController@destroy');

    }


    /**
     * Change to the user's requested role.
     * Note: That this may redirect to HOME (or display a permissions error)
     *       if the new role does not have access to the page the user is already on.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function role(Request $request)
    {

        // If a role is passed in the request, then call the method to change the user's acting role.
        if (!empty($request->role)) {
            Auth()->user()->setActingRole($request->role);
        }

        //TODO: Flash the role change. This is redundant to the user's notification popup.
        // Is there any use case to keep this and if so, should we include
        // a config setting to enable or disable it?
        session()->flash('message', 'User role changed to <strong>' . ehRole::find($request->role)->name . '</strong>.');


        // Return to the calling page.
        return redirect(url()->previous());

    }


    /**
     * Used for both update and store data validation and consistency.
     *
     * @return void
     */
    protected function dataConsistencyCheck($request, $user)
    {

        // User Profile data validation and custom error messages as needed.

        $validation_rules = [];

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Validation rules for both adding new records and updating.
        $validation_rules_for_both = [
            'first_name' => 'required',
            'last_name' => 'required',
        ];


        // Validation rules for either updating or adding new records.
        if (empty($user->id)) {
            // Validation when adding a new record.
            $validation_rules = array_merge($validation_rules_for_both, [
                'email' => ['nullable', 'unique:users,email'],
                'email_personal' => ['nullable', 'required_without:email_alternate', 'unique:users,email_personal', 'unique:users,email_alternate'],
                'email_alternate' => ['nullable', 'unique:users,email_alternate'],
            ]);
        } else {


            // Validation when updating a record.
            $validation_rules = array_merge($validation_rules_for_both, [
                'email' => [ 'nullable', Rule::unique('users')->ignore($user) ],
                'email_personal' => [
                    'nullable',
                    'required_without:email_alternate',
                    Rule::unique('users')->ignore($user),
                    new CheckEmails ],

                'email_alternate' => ['nullable', Rule::unique('users')->ignore($user)],

                // Unique across other user's email records.
                // function (string $attribute, mixed $value, Closure $fail) {} -- moved to the CheckEmails class

            ]);

        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Standard Laravel validation (customized above for either insert or updating).
        $validated = $request->validate($validation_rules);



        // BUSINESS RULES
        ///////////////////////////////////////////////////////////////////////////////////////////

        ///////////////////////////////////////////////////////////////////////////////////////////
        // WHEN ADDING: Skip any business rules you don't want to run when adding a new record.
        // (If there is no id, then this should be a new record.)
        if (!empty($user->id)) {

            // BUSINESS RULES (skip when adding):
            ///////////////////////////////////////////////////////////////////////////////////////////
            // Place any business rules here that will be SKIPPED when adding a new record.
            // (they will be run when updating.)

        }


        // BUSINESS RULES (for both adding and update):
        // Place any business rules here that will RUN when both adding AND updating a record.
        ///////////////////////////////////////////////////////////////////////////////////////////


        // RULE GROUP: "If Login is Active"
        if ($request->login_active) {       // Check these rules only if the login is active.

            // RULE 1. Verify (and create as needed) a unique username.
            // Do this only if the "name" field is missing or changed.
            if (empty($user->name) || ($user->name != $request->name)) {
                // This method contains the algorithm for checking and creating unique user names.
                $user->name = $user->uniqueUserName($request);
            }

            // RULE 2. If user only has one role assigned then set that to the default_role automatically.
            if (count($user->getUserRoles($user->id)) == 1) {
                // Pull out the role_id from the role_lookup returned array.
                $request->merge(['default_role' => $user->getUserRoles($user->id)[0]->role_id]);
            }

            // RULE 3. If user has more than one role and no default_role,
            //     then set it to the first role in the list.
            if (count($user->getUserRoles($user->id)) > 1 && ($user->default_role == '' || $user->default_role == null)) {
                // Pull out the role_id from the first role returned from the role_lookup array.
                $request->merge(['default_role' => $user->getUserRoles($user->id)[0]->role_id]);
            }

            // RULE 4. If acting_role is blank then set it to the default_role.
            if ($user->acting_role == '' || $user->acting_role == null) {
                $request->merge(['acting_role' => $request->default_role]);
            }

            // RULE 5. Set the "registered" email (as necessary).
            if (empty($user->email)) {

                // Do we have an alternate email? (Remember that either alternate or personal is required by the standard validation.)
                // Only do this if the [registered] email has not been previously set.
                if (!empty($request->email_alternate)) {
                    $request->merge(['email' => $request->email_alternate]);
                } else {
                    // Otherwise use the personal email.
                    $request->merge(['email' => $request->email_personal]);
                }

            }

            // RULE 6. If default role is blank then assign one.
            // Use the default role defined in the config file or id #4- NO ACCESS.
            if (empty($request->default_role)) {

                if (!empty(ehConfig::get('new_user_role'))) {
                    // Use the eco-helpers config file setting for the default new user role.
                    //TODO: make sure this role exists a throw an error.
                    $request->merge(['default_role' => ehConfig::get('new_user_role')]);
                } else {
                    // id #4 is the sample data "NO ACCESS" role.
                    //TODO: make sure this role exists a throw an error.
                    $request->merge(['default_role' => 4]);
                }

            }

        } // end of RULE GROUP: "If Login is Active"


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Any other rules that happen if you're login is not active.

        // RULE 7. Is user Archived, then make sure to disable their login.
        if ($request->archived) {
            $request->merge(['login_active' => 0]);
        }


        return $request;
    }


}