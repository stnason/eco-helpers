<?php

namespace ScottNason\EcoHelpers\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

use ScottNason\EcoHelpers\Classes\ehLayout;
use ScottNason\EcoHelpers\Classes\ehLinkbar;
use ScottNason\EcoHelpers\Models\ehUser;
use ScottNason\EcoHelpers\Models\ehRole;



class ehUsersController extends ehBaseController
{

    // Note: using the same traits here as we do on ehUser -- so we can take advantage of the role functions.
    // ????? Not sure what we did here -- Maybe just moved everything into calls to the ehUser class??
    //use ehUserFunctions;


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // Instead of a list -- just go to the logged in person's record.
        // Of course this assumes that you have to be logged in to even access this features so there is no error checking on Auth().

        if (Auth()->guest()) {
            return redirect('/users/' . ehUser::first()->id);
        } else {
            return redirect('/users/' . Auth()->user()->id);
        }

    }


    public function create() {
        dd('ehUsersController@create');
    }

    public function store() {
        dd('ehUsersController@store');
    }

    public function show(Request $request, ehUser $user)
    {

        //$user->deleteRoleFromUser($request,'791');

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
        ehLayout::setOptionBlock('<img alt="'.$user->id.'" title="'.$user->fullName().'" src="'.ehUser::getUserPhoto($user->id).'">');


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
        $form['layout']['form_action'] = config('app.url').'/users/'.$user->id;


        // Get all the groups that I'm a member of:
        $form['my_roles'] = $user->getUserRoles($user->id);


        // Set the radio button for the default_role
        // The template used the 'my_roles' array to loop through and decide which one is checked.


        return view('ecoHelpers::users.user-detail',[
            'form' => $form,
            'user' => $user,
            //'this_page' => $this->form['pageList']
        ]);

    }





    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \ScottNason\EcoHelpers\Models\ehUser $user
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
            $request->merge(['password'=>Hash::make($request->password)]);
        } else {

            // If password is the same, then just take it out of the $request and leave it alone.
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
            session()->flash('message','User profile for <strong>'.$user->fullName().'</strong> saved successfully. ');
        } else {
            session()->flash('message','Something went wrong.');
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Go back to the detail page.
        return redirect('/users/'.$user->id);

    }

    public function destroy() {
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
    public function role(Request $request) {


        // If a role is passed in the request, then call the method to change the user's acting role.
        if (!empty($request->role)) {
            Auth()->user()->setActingRole($request->role);
        }


        //TODO: This is redundant to the user's notification popup.
        // Is there any use case to keep this and if so, should we include
        // a config setting to enable or disable it?
        session()->flash('message','User role changed to <strong>'.ehRole::find($request->role)->name.'</strong>.');


        // Return to the calling page.
        return redirect(url()->previous());

    }




    /**
     * Used for both update and store data validation and consistency.
     *
     * @return void
     */
    protected function dataConsistencyCheck($request, $user) {

        // User Profile data validation and custom error messages as needed.

        // Require a name - unique user name, etc. .... ??
        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',              // for now, this is the login user name so we need it.
                                                // Or we could require a regular email address then just populate
                                                // the 'email' field from that. (?)
        ]);

        // DON'T CHECK THESE RULES WHEN ADDING A NEW RECORD:


/*
        dd($user->id,
            $user->getUserRoles($user->id),
            $user->getUserRoles($user->id)[0]->role_id
        );
*/

        // 1. If user only has one role assigned then set that to the default_role automatically.
        if (count($user->getUserRoles($user->id)) == 1) {
            // Pull out the role_id from the role_lookup returned array.
            $request->merge(['default_role'=>$user->getUserRoles($user->id)[0]->role_id]);
        }

        // 2. If user only has more than one role and no default_role,
        //      and the default_role is blank - then set it to the first role in the list.
        if (count($user->getUserRoles($user->id)) > 1 && ($user->default_role == '' || $user->default_role == null)) {
            // Pull out the role_id from the first role returned from the role_lookup array.
            $request->merge(['default_role'=>$user->getUserRoles($user->id)[0]->role_id]);
        }

        // 3. If acting_role is blank then set it to the default_role.
        if ($user->acting_role == '' || $user->acting_role == null) {
            $request->merge(['acting_role'=>$request->default_role]);
        }


        return $request;
    }


}