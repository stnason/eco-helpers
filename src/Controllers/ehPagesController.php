<?php

namespace ScottNason\EcoHelpers\Controllers;

use ScottNason\EcoHelpers\Classes\ehMenus;
use ScottNason\EcoHelpers\Classes\ehAccess;
use ScottNason\EcoHelpers\Classes\ehLinkbar;
use ScottNason\EcoHelpers\Models\ehPage;
use ScottNason\EcoHelpers\Classes\ehLayout;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


//TODO: there is no visual indication of page not being active when in the page detail. (add something)

/**
 * The Controller responsible for managing the crud interaction with the Menus/Pages entries in the eh_pages table.
 */
class ehPagesController extends ehBaseController
{

    // This is displayed on the page list and page detail pages over the tree view and legend boxes.
    // It explains the number dot dash combinations that appear in the view.
    // Doing it here since it appears in at least 3 different spots.
    protected $tree_layout_explanation = '<span class="fw-light"><em>(order.id-name)</em></span>';


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        ehLayout::initLayout();

        ehLayout::setDynamic(false);
        ehLayout::setOptionBlock(false);
        ehLayout::setAttention(false);
        ehLayout::setFullWidth(false);


        $linkbar = new ehLinkbar();
        //$linkbar->setHideExportAll(true);
        $linkbar->setExportTableName('eh_pages');
        ehLayout::setLinkbar($linkbar->getLinkbar());


        $form['layout'] = ehLayout::getLayout();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // The normal layout['menus'] returned is only going to be active menu items
        // So we're going to have to get one that has all entries in it.
        $menu = new ehMenus(false);

        // Pull the menus collection from the new $menus object.
        $pages_all = $menu->getPages();

        // Pull the menus "Legend" from the eco-menus config file.
        $pages_legend = $menu->getLegend();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // The Legend and Page Tree display explanation (set at top of this controller).
        $form['tree_layout_explanation'] = $this->tree_layout_explanation;


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Add the menu list "display classes" to the returned menu collection
        // Note: this is only used for the menu/pages index and for the menu/pages detail.

        // Note: the display_class is already added in the ehMenus class. No need to call it again here.
        //$form['layout']['pages_all'] = ehMenus::addDisplayClass($pages_all);
        //$form['layout']['pages_legend'] = ehMenus::addDisplayClass($pages_legend);
        $form['layout']['pages_all'] = $pages_all;
        $form['layout']['pages_legend'] = $pages_legend;


        ///////////////////////////////////////////////////////////////////////////////////////////
        return view('ecoHelpers::core-admin.page-index',[
            'form' => $form
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // Using an empty model to allow the form to operate properly.
        $page = new ehPage();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Reset the display areas to defaults and pull the page info from Pages
        ehLayout::initLayout();

        ehLayout::setOptionBlock(false);
        ehLayout::setDynamic(false);
        ehLayout::setAttention(false);
        ehLayout::setWhenAdding(true);

        $linkbar = new ehLinkbar();
        ehLayout::setLinkbar($linkbar->getLinkbar());


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set up Save button based on this user's permissions.
        ehLayout::setStandardButtons('save');


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Pull the $form array to pass to the template.
        $form['layout'] = ehLayout::getLayout();


        // Set the form action  --  NOTE: POST to /groups calls groups.store
        $form['layout']['form_action'] = config('app.url').'/pages';
        $form['layout']['form_method']='POST';

        // Signal to template to leave certain elements out during a new record add.
        $form['layout']['when_adding'] = true;

        ////////////////////////////////////////////////////////////////////////////////////////////
        return view('ecoHelpers::core-admin.page-detail',[
            'form' => $form,
            'page' => $page
        ]);
    }

    /**
     * Store a newly created resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Run the data consistency check
        $consistency_message = $this->dataConsistencyCheck($request);

        ///////////////////////////////////////////////////////////////////////////////////////////
        $page = new ehPage($request->input());
        $result = $page->save();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say so.
        if ($result) {
            $the_message = 'Menu/Page item <strong>'.$page->id.'-'.$page->name.'</strong> added successfully.'.$consistency_message;
        } else {
            $the_message = 'Something went wrong.';
        }

        // Redisplay the changed data along with the flash message.
        return redirect('/pages/'.$page->id)->with('message',$the_message);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ehPage $page
     * @return \Illuminate\Http\Response
     */
    public function show(ehPage $page)
    {

        ehLayout::initLayout();
        ehLayout::setOptionBlock(false);
        ehLayout::setFullWidth(false);

        $linkbar = new ehLinkbar();
        //$linkbar->setHideExportAll(true);
        $linkbar->setExportTableName('eh_pages');
        ehLayout::setLinkbar($linkbar->getLinkbar());


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Create the Dynamic header.
        $delimiter = '<span class="fw-light"> | </span>';
        $display_parent = 'TOP LEVEL';     // Default if no parent id.
        if ($page->parent_id > 0) { $display_parent =  $page->parent_id.'-'.ehPage::find($page->parent_id)->name; }

        switch ($page->security) {
            case 1:
                $display_security = 'PUBLIC';
                break;
            case 2:
                $display_security = 'AUTHENTICATED';
                break;
            case 3:
                $display_security = 'PERMISSIONS CHECK';
                break;

            default:
                $display_security = 'NO ACCESS';
        }
        $display_menu_item = 'NO';
        if ($page->menu_item == 1) { $display_menu_item = 'YES'; }

        ehLayout::setDynamic(
            $page->id.'-'.$page->name
                  .$delimiter.'<em class="fw-light">Parent: </em>'
                  .$display_parent
                  .$delimiter.'<em class="fw-light">Access: </em>'
                  .$display_security
                  .$delimiter.'<em class="fw-light">Type: </em>'
                  .strtoupper($page->type)
                  .$delimiter.'<em class="fw-light">Menu Item: </em>'
                  .$display_menu_item
                  .$delimiter.'<em class="fw-light">Route: /</em>'
                  .$page->route

        );


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the menu name in the dynamic header.
        if ($page->active) {
            //ehLayout::setAttention($page->id.'-<span class="fw-bold">'.$page->name.'</span> is currently active.', 'bg-secondary');
            ehLayout::setAttention('Active','bg-secondary');
        } else {
            //ehLayout::setAttention($$page->id.'-<span class="fw-bold">'.$page->name.'</span> is currently inactive.', 'bg-warning');
            ehLayout::setAttention('Not Active','bg-warning');
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set up Save, New and Delete buttons based on this user's permissions.
        ehLayout::setStandardButtons();


        $form['layout'] = ehLayout::getLayout();

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the <form> action and method.
        $form['layout']['form_action'] = config('app.url').'/pages/'.$page->id;
        $form['layout']['form_method'] = 'PATCH';


        ///////////////////////////////////////////////////////////////////////////////////////////
        // The Legend and Page Tree display explanation (set at top of this controller).
        $form['tree_layout_explanation'] = $this->tree_layout_explanation;



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Prepare this menu items whole module structure for display in the sidebar.
        // Get a master dataset of all to parse and pull out the individual modules
        $menus = new ehMenus(false);
        $pages_all = $menus->getPages();

        // Who is the parent / base module over this whole menu/pages tree?
        $tmp = ehMenus::getMyModule($page->parent_id);

        // Assuming that if nothing is returned then this is the base module. (for now-see the function for TODOs)
        if ($tmp->count() == 0) {
            $id = $page->id;
        } else {
            $id = $tmp->id;
        }
        $form['module_id'] = $id;       // Used to keep the Go-To Modules dropdown in sync with the currently selected module.

        // Pull out just this module (note: all the children were already added when we instantiated the menu).
        $form['whole_module']  = $pages_all->where('id',$id)->toArray();



        ///////////////////////////////////////////////////////////////////////////////////////////
        return view('ecoHelpers::core-admin.page-detail',[
            'form' => $form,
            'page'=>$page
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ehPage $page
     * @return \Illuminate\Http\Response
     */
    public function edit(ehPage $page)
    {
        dd('PagesController@edit()');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ehPage $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ehPage $page)
    {
        // Crud Router - new
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Watch for a 'new' button submit then redirect to create which will change the form method for the next submit
        if($request->has('new')){
            return redirect('/pages/create');
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Run the data consistency check
        $consistency_message = $this->dataConsistencyCheck($request);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Update the menu table.
        $result = $page->update($request->input());            // This works for all as long as you exclude the buttons in the $guarded list


        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say so to the flash message.
        if ($result) {
            session()->flash('message','<strong>'.ucfirst($page->type).'</strong>, <strong>'.$page->id.'-'.$page->name.'</strong> updated successfully. '.$consistency_message);
        } else {
            session()->flash('message','Something went wrong.');
        }

        return redirect('/pages/'.$page->id);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ehPage $page
     * @return \Illuminate\Http\Response
     */
    public function destroy(ehPage $page)
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // DELETION RULES:
        // I can only think of this one condition to NOT delete a page.
        //  (anything else that may come up can be placed under this one.)


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 1. You can't delete any item that has children
        //      (which should only be modules or submenus).
        if (ehMenus::getMyChildren($page->id)->count()) {
            throw ValidationException::withMessages(['type' =>
                "You can't delete a page that has <strong>child entries</strong>.
                You must <strong>reassign</strong> or <strong>delete</strong> those pages first.
            "]);
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 2. You can't delete any item that is assigned as a default_home page for any role.
        // Build a query to find out
        // BUT -- IS DEFAULT HOME PAGE AN ID NUMBER OR A ROUTE !??

        $result = DB::select('SELECT * FROM eh_roles WHERE default_home_page = '.$page->id.';');
        if (count($result)>0) {
            throw ValidationException::withMessages(['type' =>
                // Might be helpful to include a link--at least to the first--offending role.
                "You can't delete a page being used as a Default Home Page in a Role."
            ]);
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 3. Anything else?
        //


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Then go ahead and delete this Page entry.
        $result = $page->delete();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // If all went okay then say so to the flash message.
        if ($result) {
            session()->flash('message','Page item <strong>'.$page->id.'-'.$page->name.'</strong> has been deleted.');
        } else {
            session()->flash('message','Something went wrong.');
        }

        return redirect('/pages');
    }



    /**
     * Check for various data consistency before storing or updating.
     * Returns any transient error Warnings to be included in the flash message.
     *
     * @param Request $request
     * @return string
     */
    protected function dataConsistencyCheck(Request $request) {

        // NOTE: These checks are used in both @store() and @update().
        $message = '';      // Any Warning message generated after checking here.

        //TODO: need to test this and see if there are any rules left to implement or not.

        // Build out Module rules:
        //                  -> must be a menu_item
        //                  -> name must be unique
        //                  -> route ?? (it's not real [or is it??] so how do we deal with this?
        //                      can't blank it out since it's needed to store the token; maybe just slugify the name?)

        // This is a conceptual design consideration.
        //  Should the consistency rules decide what "type" this is
        //  and set it automatically in these cases?
        // 'module' = menu_item = 1, parent_id is empty, children is not empty.     (no route required - use module.page->id)
        // 'submenu' = menu_item = 1, parent_id is not empty, children is not empty.    (no route required - use submenu.page->id)
        // .
        // But then rules will have to wipe out those "fake" routes if the type changes to something else.
        // .
        // If this is a module or a submenu, then route_type = '' and route (technically required) will be generated for us;
        // If this is not a route or a submenu then route is required

        // Note: for :AUTO-SET rules, use $request->merge(['whatever_key' => 1]);


        /*
        // xx. RULE - custom validation rule/mechanism template.
        ///////////////////////////////////////////////////////////////////////////////////////////
        $input = request()->all();
        Validator::extend('parentId', function($attribute, $value) use (&$input)
        {
            // $attribute is the field name and $value is the form entered (posted) value.
            // dd($attribute, $value, $input);
            return true;

        });
        */

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 1. RULE:AUTO-SET; When changing an entry from a submenu or module to something else
        //           then blank out the route so the validation forces it to be entered.
        //           Note: This rule has to run before the Module and Submenu Aut-set rules which can overwrite it!
        if ($request->route == 'module.'.$request->id || $request->route == 'submenu.'.$request->id) {
            // looks like this used to be a module or submenu -- so wipe out the route.
            $request->merge(['route' => '']);
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 2. RULE:AUTO-SET; For Modules (set this before the Laravel standard validation).
        if ($request->type == 'module') {

            // 2a. RULE - Auto set the module route name to "module.page_id".
            if (empty($request->id)) {
                // if we're adding a new record then page is "will be" one higher than highest page id
                $q = "SELECT id from eh_pages ORDER BY id desc LIMIT 1";
                $result = DB::select($q);
                $page_id = $result[0]->id + 1;      // Add 1 to the highest id on file.
            } else {
                $page_id = $request->id;
            }
            // Since routes are required, making up a "fake" route for Modules ("module" + page_id).
            $request->merge(['route' => 'module'.".".$page_id]);


            // 2b. RULE - Modules must be at the TOP LEVEL (which means they have no parent).
            if ($request->parent_id > 0) {
                // parent_id for modules must be blank or 0.
                $request->merge(['parent_id' => 0]);
            }

        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 3. RULE - Auto-set; For Submenus (set this before the Laravel standard validation).
        if ($request->type == 'submenu') {

            // Auto set the submenu route name to "submenu.page_id".
            if (empty($request->id)) {
                // if we're adding a new record then page is "will be" one higher than highest page id
                $q = "SELECT id from pages ORDER BY id desc LIMIT 1";
                $result = DB::select($q);
                $page_id = $result[0]->id + 1;      // Add 1 to the highest id on file.
            } else {
                $page_id = $request->id;
            }
            // Since routes are required, making up a "fake" route for Submenus ("submenu" + page_id).
            $request->merge(['route' => 'submenu'.".".$page_id]);

        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 4. Laravel validation rules (with custom messages):
        // Note: run any "aut-set" rules before this check.)
        $request->validate(
            [
                // Laravel stock validation rules
                'name'=> 'required',
                'route' => 'required',

                //'type' => 'parentId'          // just testing, but this custom validation mechanism does work.
                                                // See 0. RULE - custom validation above.
            ],

            [
                // Custom validation messages:
                'name.required'=>'All page entries must have a <strong>Name</strong>. Please enter a <strong>Name</strong> to continue.',
                'route.required'=>'This type of page entry must have a <strong>Route</strong>. Please enter a <strong>Route</strong> to continue.',

                //'type.parent_id'=>'All items other than <strong>Modules</strong> must have a <strong>Parent ID</strong>.',
            ]
        );


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 5. RULE:AUTO-SET - remove Font Awesome <i> tags if they were inadvertently copied in to the field.
        // And then strip of the <i> tag and just leave the class by itself.
        // <i class="fa-solid fa-wheat-awn"></i>
        // Note: this is duplicated in the ehLayout::setIcon section and maybe could be combined/simplified.
        $tmp = $request->input('icon');
        $tmp = str_replace('<i class="', "", $tmp);
        $tmp = str_replace('"></i>', "", $tmp);
        $request->merge(['icon'=>$tmp]);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 6. RULE:AUTO-SET - Normalize the route name before saving,
        // And then replace the 'route' value directly in the $request.
        $request->merge(['route' => ehPage::normalizeRouteName($request->route)]);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 7. RULE - Check to see if the specified route is defined or not.
        // BUT - do not do this check for submenus or modules since they both use a "fake" route.
        // And if not, then create a Warning message for the calling method to include in the flash message.
        $message = '';
        if (
            $request->type != 'module' &&
            $request->type != 'submenu'
        ) {       // Note, that if this is a module or submenu, the route is fake (type.page_id).

            // For resource routes:
            if ($request->type == 'resource') {
                if (
                    !Route::has($request->route.'.index') &&
                    !Route::has($request->route.'.create') &&
                    !Route::has($request->route.'.store') &&
                    !Route::has($request->route.'.update') &&
                    !Route::has($request->route.'.show') &&
                    !Route::has($request->route.'.destroy')
                ) {
                    $message = ' (<strong>Warning!</strong> No Resource routes exist for "<strong>' . $request->route . '</strong>".)';
                }
                // For all other, non-resource routes:
            } elseif (!Route::has($request->route)) {
                $message = ' (<strong>Warning!</strong> Route "<strong>' . $request->route . '</strong>" does not exist.)';
            }
        }
        return $message;

    }


    /**
     * Save the page entry data after a successful onscreen drag-n-drop operation.
     *
     * @param Request $request
     * @return mixed
     */
    public function saveDrag(Request $request)
    {

        $page = ehPage::find($request->input('id'));

        $page->order = $request->input('order');
        $page->parent_id = $request->input('parent_id');

        return $page->save();

    }



}

