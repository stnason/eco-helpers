<?php

namespace ScottNason\EcoHelpers\Controllers;


use App\Classes\ValidList;          // Use the package published version (not ehValidList internal).
use App\Models\User;                // Using this to access the ehUserFunctions trait methods.

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\CustomRule;
use Closure;
use ScottNason\EcoHelpers\Classes\ehAccess;
use ScottNason\EcoHelpers\Classes\ehLinkbar;
use ScottNason\EcoHelpers\Classes\ehMenus;
use ScottNason\EcoHelpers\Classes\ehConfig;
use ScottNason\EcoHelpers\Models\ehRole;
use ScottNason\EcoHelpers\Classes\ehLayout;


/**
 * The Controller responsible for managing the crud interaction with the roles and permissions
 * in the eh_roles, eh_roles_lookup and eh_access_tokens tables.
 *
 */
class ehRolesController extends ehBaseController
{

    ///////////////////////////////////////////////////////////////////////////////////////////
    // Set the page token bits
    // the internal bit names (rights) used for each page along with their SEC_CONSTANT
    protected $page_bit_names = [];         // Set in the __constructor below.
    protected $disabled_flag = false;       // True/False; Used by the show() method after checking the module view status.
    

    public function __construct()
    {
        parent::__construct();              // Make sure and include anything from the extended ehBaseController.

        // Set the page token bits (these are used on each permission checkbox and some of the looping controls below.)
        // These are the internal bit names (access rights) used for each page along with their SECURITY LEVEL constant.
        $this->page_bit_names = [
            'page_bit_view'=>ACCESS_VIEW,
            'page_bit_export_restricted'=>ACCESS_EXPORT_RESTRICTED,
            'page_bit_export_displayed'=>ACCESS_EXPORT_DISPLAYED,
            'page_bit_edit'=>ACCESS_EDIT,
            'page_bit_add'=>ACCESS_ADD,
            'page_bit_delete'=>ACCESS_DELETE,
            'page_bit_export_table'=>ACCESS_EXPORT_TABLE,
            'page_bit_feature_1'=>ACCESS_FEATURE_1,
            'page_bit_feature_2'=>ACCESS_FEATURE_2,
            'page_bit_feature_3'=>ACCESS_FEATURE_3,
            'page_bit_feature_4'=>ACCESS_FEATURE_4,
        ];

    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // layout the layout options
        ehLayout::initLayout();
        ehLayout::setOptionBlock(false);
        ehLayout::setDynamic(false);
        ehLayout::setAttention(false);

        $linkbar = new ehLinkbar();
        ehLayout::setLinkbar($linkbar->getLinkbar());


        ///////////////////////////////////////////////////////////////////////////////////////////
        ehLayout::setAutoload('datatables');         // Include the form data changed check on any crud page.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Retrieve the layout options
        $form['layout'] = ehLayout::getLayout();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Get the Group data list
        $form['roles'] = ehRole::all();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Define the fields to be used in the table/list view.
        $form['use_fields'] = [
            'id' => 'Role ID',
            'active' => 'Active',
            'site_admin' => 'Site Admin',
            'name' => 'Name',
            'description' => 'Description',
            'restrict_flag' => 'Restriction',
            'default_home_page' => 'Home Page',
        ];


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Call the Roles table list view.
        return view('ecoHelpers::core-admin.role-index',[
            'form' => $form
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(ehRole $role)
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Create an empty model to use for this "add" operation.
        // Looks like either this method -- or the type hinting in create(Model $model), works to get us started.
        // Although this method may be a little more verbose and clear about what's happening. (?)
        // $page = new ehRole();

        ///////////////////////////////////////////////////////////////////////////////////////////
        // layout the layout options
        ehLayout::initLayout();
        ehLayout::setOptionBlock(false);

        $linkbar = new ehLinkbar();
        ehLayout::setLinkbar($linkbar->getLinkbar());

        // SECURITY: Button control.
        if (ehAccess::chkUserResourceAccess(Auth()->user(),Route::currentRouteName(),ACCESS_EDIT)) {
            ehLayout::setButton('save', '<input class="btn btn-primary" type="submit" id="save" name="save" value="Save">');
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Retrieve the Layout
        $form['layout'] = ehLayout::getLayout();

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the form action
        $form['layout']['form_action'] =  route('roles.store');
        $form['layout']['form_method'] = 'POST';
        $form['layout']['when_adding'] = true;           // May toggles parts of the form off when adding a new record.

        ///////////////////////////////////////////////////////////////////////////////////////////
        return view('ecoHelpers::core-admin.role-detail',[
            'form' => $form,
            'role'=>$role
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, ehRole $role)
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Run the data consistency check
        $consistency_message = $this->dataConsistencyCheck($request, $role);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Just type hinting in the model that we type hinted in from the create method.
        $role->fill($request->input());
        $result = $role->save();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say so.
        if ($result) {
            $the_message = 'Role <strong>'.$role->id.'-'.$role->name.'</strong> added successfully.'.$consistency_message;
        } else {
            $the_message = 'Something went wrong.';
        }

        // Redisplay the changed data along with the flash message.
        return redirect(route('roles.show',[$role->id]))->with('message',$the_message);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ehRole $role
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, ehRole $role)
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // layout the layout options
        ehLayout::initLayout();
        ehLayout::setOptionBlock(false);

        $linkbar = new ehLinkbar();
        ehLayout::setLinkbar($linkbar->getLinkbar());

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the menu name in the dynamic header.
        if ($role->active) {
            ehLayout::setAttention('Active', 'bg-success');
        } else {
            ehLayout::setAttention('Not Active', 'bg-warning');
        }

        $form['site_admin_class'] = '';
        if ($role->site_admin) {
            ehLayout::setAttention('This Role has complete <strong>Site Admin</strong> control!', 'bg-danger');
            $form['site_admin_class'] = 'bg-warning';
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the dynamic header tole name.
        ehLayout::setDynamic($role->name);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // CHECK FOR EDIT LOCK (based on the 'lock' column in the eh_roles table).
        // Editing ADMIN and NO ACCESS roles is not allowed.
        // (let the template know so it can disable the fields.)
        // Note: this is also protected in the data consistency rules at the bottom.
        $form['role_is_locked'] = false;
        if ($role->locked) {
            $form['role_is_locked'] = true;
            if (ehAccess::chkUserResourceAccess(Auth()->user(),Route::currentRouteName(),ACCESS_ADD)) {
                // Create buttons: Build out only a 'New' button (No Save or Delete).
                ehLayout::setButton('new', '<input class="btn btn-primary" type="submit" id="new" name="new" value="New">');
            }
            // Add the "not editable" message to the dynamic header..
            ehLayout::setDynamic($role->name.' - [role cannot be edited]');
        } else {
            // Create buttons: Build out all edit buttons for this user's current role permissions.
            ehLayout::setStandardButtons();
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        $form['layout'] = ehLayout::getLayout();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the form action
        $form['layout']['form_action'] = route('roles.update',[$role->id]);
        $form['layout']['form_method'] = 'PATCH';
        $form['layout']['when_adding'] = false;           // May toggle parts of the form off when adding a new record.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 1. Was there a module posted from the form? (if so, use it.)
        if ($request->module_id) {
            $module_id = $request->module_id;
        } else {

            // Otherwise this must be the first time through the form.
            // Pull a list of modules and pick the first one (in 'order').


            //TODO: Design consideration: if you pull just a modules list this leaves out any top level/ page only items. (?)
            // ehRolesController@show() is using the mbit to decide if you have permissions on this "module" -- not sure if this can be changed.
            $module_list = ValidList::getList('module_list_all');
            //$module_list = ValidList::getList('top_level_list');      // this messes with the actual permissions per page then (there is none!)


            reset($module_list);         // Leaves only the first element in the array.
            $module_id = key($module_list);     // Then pulls the key for that first element.

        }
        $form['module_id'] = $module_id;        // The template uses this to preselect the Module list dropdown.



        ///////////////////////////////////////////////////////////////////////////////////////////
        // 2. Set the m_bit_view bit for the "Do we have access to View this Module?" checkbox.
        // Note: has to be checked and set before getPageList() below (it uses the disabled flag)
        $form['m_bit_view'] = '';
        $this->disabled_flag = 'disabled';              // If we don't have access to this Module then all the checkboxes are grayed out.
        // Using $this global since it's needed down below in the getPageList() function.
        // Check if this whole module has VIEW access for this role.

        if (ehAccess::chkRoleSecurityAccess($role, $module_id, ACCESS_VIEW)) {
            $form['m_bit_view'] = 'checked';
            $this->disabled_flag = '';
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Get the list of pages that go with this Module
        // Note: this is dependent on the result of the chkRoleSecurityAccess() above.
        $form['page_list'] = $this->getPageListByModule($form['module_id'], $role->id);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Get a list of current users assigned to this Role.
        $form['user_list'] = User::getUsersInRole($role->id);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the default value of the copy_from drop down.
        // (as set in the eco-helpers config file)
        // This should be a role with very low privileges -- maybe like a read only group (just to be safe).
        $form['default_copy_from_role_id'] = ehConfig::get('default_copy_from_role_id');



        ///////////////////////////////////////////////////////////////////////////////////////////
        return view('ecoHelpers::core-admin.role-detail',[
            'form' => $form,
            'role'=>$role
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ehRole $role
     * @return \Illuminate\Http\Response
     */
    public function edit(ehRole $role)
    {
        dd('GroupsController@edit()');

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ehRole $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ehRole $role)
    {
        // Crud Router - new
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Watch for a 'new' button submit then redirect to create which will change the form method for the next submit
        if($request->has('new')){
            return redirect(route('roles.create'));
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Run the data consistency check
        $consistency_message = $this->dataConsistencyCheck($request, $role);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Update the roles table.
        $result = $role->update($request->input());


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Update all Access Tokens for the currently displayed module.
        //  (first have to delete all)
        //  $role_id is this Role we're currently editing; $role->id;


        // 0. This is needed when making the Module Not Active (an empty checkbox will not post a value)
        // 1. First create the Module (View) token
        if (isset($request['m_bit_view'])) {
            $access_token_array_modules['view'] = $request['m_bit_view'];  // Could use ACCESS_VIEW too.
        } else {
            $access_token_array_modules['view'] = 0;
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 2. Get the same working page list for the module that the show() method was using.
        $working_page_list = $this->getPageListByModule($request->frm_module_list, $role->id);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 3. Then add a placeholder for the eventual $token_value (needs to initialize here so we can sum on the fly below).
        foreach ($working_page_list as $page_id=>$info) {
            $working_page_list[$page_id]['token_summed_value'] = 0;

            // 4. And while we're at it, let's delete all of the access tokens for this page
            //      since the loop below skips non-posted (non-checked) entries.
            ehAccess::deleteAccessToken($page_id, $role->id);                                // By page_id
        }



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Loop the $page_bit names (defined at top) -- then inside each loop --
        // loop the $working_page_list and sum the associated token values from the $request.

        foreach ($this->page_bit_names as $page_bit_name=>$value) {     // Note: $value is a throwaway here. We just want the $page_bit_name.

            // If it's empty that means the checkbox wasn't posted -- so it wasn't checked.
            // Skipping so the foreach() doesn't crash on a null.
            if (!empty($request->input($page_bit_name))) {


                // If there's posted value for this security bit, that means that at least one page has something checked.
                // so loop the posted $page_bit_array and then add it to the summed $token_value for that $page_id.
                foreach ($request->input($page_bit_name) as $page_id=>$token_value) {

                    // Add the $token_value from the $request to the $token_value for this $page_id.
                    $working_page_list[$page_id]['token_summed_value'] = $working_page_list[$page_id]['token_summed_value'] + $token_value;

                }
            }

            ///////////////////////////////////////////////////////////////////////////////////////////
            // Save the pages access (saveAccessToken for each page in the posted array for this security bit.
            // Can accept either a complete encoded integer or an array)

            foreach ($working_page_list as $page_id=>$page) {
                ehAccess::saveAccessToken($page_id, $page['token_summed_value'], $role->id);
            }

        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Save the Modules access (saveAccessToken can accept either a complete encoded integer or an array)
        // Get the route name for the currently selected Module
        $module_page_id = $request->input('frm_module_list');   // Just use the page number of the Module we're working on.
        ehAccess::saveAccessToken($module_page_id, array_sum($access_token_array_modules), $role->id);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say so to the flash message.
        if ($result) {
            session()->flash('message','Role <strong>'.$role->name.'</strong> saved successfully. ');
        } else {
            session()->flash('message','Something went wrong.');
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Go back to the detail page.
        // Remembering to keep the module id in sync.
        return redirect('/roles/'.$role->id.'/?module_id='.$request->frm_module_list);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ehRole $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(ehRole $role)
    {

        // 0. RULE: Can't delete any 'locked' role.
        if ($role->locked) {
            session()->flash('message','<strong>Warning!</strong> The <strong>'.$role->name.'</strong> role is locked and cannot be deleted.');
            return redirect(route('roles.show',[$role->id]));
        }

        // 1. RULE: Can't delete roles that have people assigned.
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Get a list of current users assigned to this Role.
        $user_list = User::getUsersInRole($role->id);
        if (count($user_list) > 0) {
            session()->flash('message','<strong>Warning!</strong> You cannot delete a role that <strong>has Users assigned</strong>. You must first <strong>remove Users</strong> from the role before deleting it.');
            return redirect(route('roles.show',[$role->id]));
        }

        // 2. RULE: Can't delete Site Admin role (id #3)
        if ($role->id == 3) {
            session()->flash('message','<strong>Warning!</strong> You cannot delete the <strong>Site Admin</strong> role.');
            return redirect(route('roles.show',[$role->id]));
        }

        // 3. RULE: Can't delete NO ACCESS role (id #4)
        if ($role->id == 4) {
            session()->flash('message','<strong>Warning!</strong> You cannot delete the <strong>NO ACCESS</strong> role.');
            return redirect(route('roles.show',[$role->id]));
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Before deleting role, delete all access tokens associated with this role.
        // Note: The js popup in the template warns about this.
        DB::delete('DELETE FROM eh_access_tokens WHERE role_id ='.$role->id);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Delete all associated role lookups.
        // I think -- based on the rule that you can't delete a role that has ANY users assigned --
        //  that this check is redundant. (if you delete the users -> then the lookup entry is gone too.)
        // DB::delete('DELETE FROM eh_roles_lookup WHERE role_id ='.$role->id);



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Delete this role.
        $result = $role->delete();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say so to the flash message.
        if ($result) {
            session()->flash('message','Role <strong>'.$role->id.'-'.$role->name.'</strong> has been deleted.');
        } else {
            session()->flash('message','Something went wrong.');
        }

        // Return to the roles list.
        return redirect(route('roles.index'));
    }


    /**
     * Check for standard form "save" validation and apply any data consistency (auto-save/update) rules.
     * Will throw a validation error and/or change the $request variable directly as needed.
     * Can return a $consistency_message to be appended to the flash message -
     *  (as a warning or supplemental information even on a successful save).
     *
     * @param $request
     * @return string
     */
    protected function dataConsistencyCheck($request, $role) {

        $consistency_message = '';


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set any default values.
        /* Not sure what we're trying to accomplish here but this zeros out the actual ADMIN account!
        apparently we are not passing the site_admin input (?)
        if (empty($request->site_admin)) {$request->merge([
            'site_admin'=>0
        ]);}
        */


        // REMEMBER: (for closures) the $request variable IS NOT available inside the
        //           validate() method but the request() helper IS!


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 1. Laravel validation rules (with custom messages):
        // Note: run any "aut-set" rules before this check.)
        $request->validate(
            [
                // Laravel stock validation rules
                'name'=> [
                    'required',
                    Rule::unique('eh_roles')->ignore($role),

                    ///////////////////////////////////////////////////////////////////////////////////////////
                    // 2. Don't allow the default ADMIN account to be edited at all.
                    // Custom closure validation:
                    function (string $attribute, mixed $value, Closure $fail) {
                        if (request()->input('name') == 'ADMIN') {
                            $fail("The default ADMIN account cannot be edited.");
                        }
                    },

                    ///////////////////////////////////////////////////////////////////////////////////////////
                    // 3. Don't allow the default NO ACCESS account to be edited at all.
                    // Custom closure validation:
                    function (string $attribute, mixed $value, Closure $fail) {
                        if (request()->input('name') == 'NO ACCESS') {
                            $fail("The default NO ACCESS account cannot be edited.");
                        }
                    },
                ],
            ],

            [
                // Custom validation messages:
                'name.required'=>'All roles must have a <strong>Name</strong>. Please enter a <strong>Name</strong> to continue.',
            ]
        );

        return $consistency_message;
    }

















    /**
     * Get/Create the pageList array for use in the rights grid (page id, page name + all checkboxes)
     * Note: Because of the difficulty and number of decisions, this includes all of the fully built HTML checkboxes.
     * Note: This is staying here since it is only used by the ehRolesController.
     *
     */
    protected function getPageListByModule($module_id, $role_id) {

        // Create a list of page number - page name just for this module (used below to create the Rights grid)
        $pageList = [];


        $role = User::normalizeRoleID($role_id);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Get the pages for this module (TOP LEVEL) - selected in the form grid rights area dropdown.
        $m = new ehMenus($module_id, 'all');

        // Get the menu hierarchy flattened (basically a single list of ALL children under this module at any level).
        $menuList = $m->getFlatPages();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Reformat the $menuList array to have the page_id numbers as the $key (as $pageList).
        // And package up any other Menu variables that the rights_grid form will need/use.
        foreach ($menuList as $key=>$page) {

            $pageList[$page->id] = [
                //'id'=>$page->id,      // $page->id is the $key
                'name'=>$page->name,
                'security'=>$page->security,
                'feature_1'=>$page->feature_1,
                'feature_2'=>$page->feature_2,
                'feature_3'=>$page->feature_3,
                'feature_4'=>$page->feature_4,
                'comment'=>$page->comment,
                'order'=>$page->order,
                'route'=>$page->route,
                'type'=>$page->type,
                'display_class'=>$page->display_class
            ];

        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Loop each $pageList page item and decode and add the current security rights for this page.
        foreach ($pageList as $page_id=>$page) {

            // Note: decodeAccess is expecting a full route name.
            $route_name = $page['route'];
            
            // Pull any access tokens for any/all roles that you may be assigned to and then combine them into a single token.
            $combined_token = ehAccess::getAccessToken($page_id, $role->id);

            // Initialize the holding key for all of the individual access rights for this person.
            // Note: This is a temporary value on the $page loop and not saved back to $pageList
            // Note: Using 'true' here to return the values for each decoded security level.
            $page['access_right'] = ehAccess::decodeToken($combined_token, true);


            ///////////////////////////////////////////////////////////////////////////////////////////
            // Loop each access_right for this page.
            foreach ($page['access_right'] as $security_name => $token_value) {

                ///////////////////////////////////////////////////////////////////////////////////////////
                // NOTE: the $token_value for each checkbox needs to be that security value --
                // INDEPENDENT of whether or not it has a value in the $page['access_right'] array
                // constant('ACCESS_'.$security_name)
                ///////////////////////////////////////////////////////////////////////////////////////////


                // Make sure every bit checkbox starts out as unchecked.
                $checked = '';

                // Note that the ehAccess::decodeToken() method just returns true or false for each access right.
                // So just a very simple check for "is this access right true of false?" -- then check the box or leave it unchecked.
                if (array_sum($page['access_right']) != 0) {

                    if ($token_value) {
                        $checked = 'checked';
                    }

                }

                ///////////////////////////////////////////////////////////////////////////////////////////
                // Create the checkbox for this $page_bit_name
                // Note: disabled checkboxes don't post data so add a hidden one of the same name to keep the data alive while toggling the module from active to not.

                $page_bit_name = 'page_bit_'.$security_name;    // Just add the current $security name to the end.
                $pageList[$page_id][$page_bit_name] = '';


                // NOTE: The $this->disabled_flag is first set in the show() method after getting the m_bit_view state:
                // If this group has no access to the currently selected module name, then all checkboxes should be grayed out.
                // If we have no view access for this whole module then we'll just disable the rest of the checkboxes for now.
                $disabled = '';
                if ($this->disabled_flag) {
                    $disabled = 'disabled';
                }


                // Add a hidden field to keep the data alive for the re-post
                if ($disabled == 'disabled') {

                    // This one is hidden and just a placeholder for the data (not checked, disabled
                    // But only set the value if it is checked.  (Why? !! Because it will post the value when it shouldn't be there.)
                    if ($checked == 'checked') {
                        $hidden_value = constant('ACCESS_'.strtoupper($security_name));
                    } else {
                        $hidden_value = 0;
                    }

                    $pageList[$page_id][$page_bit_name] = '<input type="hidden" id="' . $page_bit_name . '[' . $page_id . ']"
                    name="' . $page_bit_name . '[' . $page_id . ']"    value="' . $hidden_value . '" >';

                    // When using the hidden one above as the placeholder, change the name and id of the dummy
                    $pageList[$page_id][$page_bit_name] .= '<input type="checkbox" id="' . $page_bit_name . '_hold[' . $page_id . ']"
                    name="' . $page_bit_name . '_hold[' . $page_id . ']"    value="' . constant('ACCESS_'.strtoupper($security_name)) . '" '.$checked.' '. $disabled.'>';

                } else {
                
                    // **** This is the "normal" not hidden one ****
                    $pageList[$page_id][$page_bit_name] = '<input type="checkbox" id="' . $page_bit_name . '[' . $page_id . ']"
                    name="' . $page_bit_name . '[' . $page_id . ']"    value="' . constant('ACCESS_'.strtoupper($security_name)) . '" '.$checked.' '. $disabled.'>';

                }

            }

        }

        return $pageList;

    }



}

