<?php

namespace ScottNason\EcoHelpers\Classes;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use ScottNason\EcoHelpers\Models\ehPage;
use ScottNason\EcoHelpers\Models\ehUser;




/**
 * ehMenus is responsible for building out the menu-ing system data for the standard nav header
 *  and those used in the Menus/Pages entry screens.
 *  It uses (and depends on) the pages table.
 *
 * !! IMPORTANT !! - This IS NOT PART OF THE BACK-END SECURITY CHECK.
 *                   SECURITY IS HANDLED THROUGH THE check_permissions MIDDLEWARE.
 *                 - ehMenus only controls what SHOWS up on the user's personal menu tree -- not the "access" to it.
 *
 */
class ehMenus
{
    /**
     * Making these object properties since the recurseChildren() will need to use the same queries.
     * @var array|mixed
     */
    protected $pages_output = [];       // The final output array built out in the __construct method (returned by getPages()).
    protected $parent_filter = '';      // The WHERE clause portion of the query as it relates to the parent_id
    protected $menu_filter = '';        // The WHERE clause portion as it relates to the active=1/0, menu_item=1/0
    protected $order_by = '';           // THE ORDER BY portion of the sql query.
    protected $apply_security = true;   // When set to false all pages will be pulled. True uses the page and user level security to restruct.


    /**
     * Constructor can build out based on 2 different $types: all / user.
     * all = no filters; no security            - used for Menus/Pages Admin; for Group Admin rights grid
     * user = active, menu items, w/security    - auth user's nav bar menus
     *
     * @param $type     // all (for Menus/Pages Admin), user security filtered (for Navbar)
     */
    public function __construct($start_id = 0, $type = 'all')
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 0. Check to see if the Menus System is enabled in the eco-helpers config file.
        // Note: $menu_output is already initialized to [] above so that's what gets returned if you call getPages().
        if (!ehConfig::get('menus.enabled')) {
            return false;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 1. Make sure the pages table exists.
        if (!Schema::hasTable('eh_pages')) {
            dd('The "eh_pages" table is missing. Either disable menus in the eco-helpers config file or run the pages migration');
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Apply the $start_id filter.
        if (empty($start_id)) {
            // This is applied to the sql query if the $start_id is empty (not passed or blank) (basically the modules tree).
            $this->parent_filter = " (parent_id = 0 OR parent_id = '' OR parent_id IS NULL) ";
        } else {
            // When passing a specific $start_id then just get the items under it (either a module or a submenu).
            $this->parent_filter = " parent_id = ".$start_id." ";
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Apply the $type=user or $type=all filter.
        if ($type == 'user') {
            // Users will only ever get active menu items that they have access to (page security is applied below).
            $this->menu_filter = ' AND (active = 1 AND menu_item = 1)';
        } else {
            // When calling for 'all' -- no filter or page security is applied.
            $this->apply_security = false;      // For now, 'all' will always assume that you want all entries (like for the ehPagesController@index).
            $this->menu_filter = '';
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Is the access system enabled or not?
        // NOTE: If the access system is disabled, no page is checked!
        //       Users will see every menu item.
        if (!ehConfig::get('access.enabled')) {
            $this->apply_security = false;
        } else {
            if ($type='user') {
                // If we're not specifically calling for 'all'
                // AND the access system is enabled -- then specifically force apply_security to on.
                $this->apply_security = true;
            }
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // ORDER; We always want to attempt to place the items in order grouped by their parent and defined order.
        $this->order_by = " ORDER BY parent_id, `order` ";

        ///////////////////////////////////////////////////////////////////////////////////////////
        $query = "SELECT * FROM eh_pages WHERE " . $this->parent_filter . $this->menu_filter . $this->order_by .";";


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Execute the query built out above ( this will be the initial parents set before adding children ).
        // Using "collect" to wrap this array result with an Illuminate\Support\Collection.
        // This allows getModules to run the ->sortBy()
        $parents = collect(\DB::select($query));


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Loop the initial parents record set and recursively get the children
        // And while we're at it, check the page and user security settings for this level (!! recurseMyChildren() will have to check too !!).
        foreach ($parents as $item) {

            // All levels of children for this parent item.
            $item->children = $this->recurseMyChildren($item->id);

            ///////////////////////////////////////////////////////////////////////////////////////////
            // Add the children to the parents and build out the $this->>output_page array (based on security settings).
            // NOTE: If the access system is disabled, no page is checked!
            //       Users will see every menu item.

            // SECURITY CHECK:
            // 0-no access, 1-public, 2-auth only, 3-full permissions check.

            // Check the security on this top level ($item) only -- checks for child security need to be performed inside of recurseMyChildren().
            // Note: that since the children array was added to this item above, this security check will drop access to the children
            //  for any module are submenu that doesn't have access.

            if ($this->apply_security && !self::pageSecurity($item)) {

                // If we're checking security and, it fails that check,
                // then do nothing (don't add this $item to the pages_output array).

            } else {

                // If we're not checking security OR this $item passed the security check, then include it.
                $this->pages_output[] = $item;
            }

        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Finally add the display class for each page item.
        $this->pages_output = $this->addDisplayClass($this->pages_output);

    }


    /**
     * For internal use pulling--recursively--the children from a given $parent_id.
     * Will use the Class property setting for the query filters and $order_by
     * along with the $apply_security setting (these are all set in the __constructor).
     *
     * @param $parent_id
     * @return array
     */
    protected function recurseMyChildren($parent_id) {

        $children = [];

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Pull a list of child entries for this parent_id
        $query = "SELECT * FROM eh_pages WHERE parent_id = {$parent_id}" . $this->menu_filter . $this->order_by .";";
        $tmp = DB::select($query);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Call recursively until there are no more downstream children for this parent_id.
        foreach ($tmp as $child) {

            $child->children = self::recurseMyChildren($child->id);

            ///////////////////////////////////////////////////////////////////////////////////////////
            // When building out the "available pages" list for the GroupsController, we want ALL entries, so skip the security check.
            if ($this->apply_security && !self::pageSecurity($child)) {
                // If we're checking security and, it fails that check, then do nothing (don't add this $item to the pages_output array).
            } else {
                $children[] = $child;       // If we're not checking security OR this $item passed the security check, then include it.
            }

        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        return $children;   // Return the $children array for inclusion in the parent items recordset
        // Note: That because of the recursion, this array could be multi-levels deep.

    }


    /**
     * For internal use. The ehMenus class is now responsible for sending out Menus data
     *  with the display_class already assigned.
     *
     * Mainly used by the Pages List and Pages Detail pages to format the page items for visual delineation.
     *  Note: this is recursive in order to add the display classes to all child levels.
     *  Note: these css styles are contained in the eh-page-list.css file.
     *
     * @param $input_pages
     * @return mixed
     */
    protected function addDisplayClass($input_pages)
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Loop the passed pages recordset and add the appropriate display class for the type
        foreach ($input_pages as $page) {


            ///////////////////////////////////////////////////////////////////////////////////////////
            // $input_pages expects the the children[] hierarchy to already be added.
            // So go ahead and recursively call this to add the display calls for all levels of children too.
            if (!empty($page->children)) {
                self::addDisplayClass($page->children);
            }

            ///////////////////////////////////////////////////////////////////////////////////////////
            // Check and apply the styling rules for the pages display
            $class = '';

            // 1. Active y/n? (or 0-no access for the security setting)
            if ($page->active != 1 or $page->security == 0) {
                $class .= ' deactive';
            }

            // 2. Type - page
            // 'page' is just the default style; so not using a specific css class.
            // Leaving this here just in case a use-case for it comes up.
            // if ($page->type == 'page') {$class .= ' type-page';}

            // 3. Type - method call
            if ($page->type == 'method') {
                $class .= ' type-method';
            }

            // 4. Type - resource
            if ($page->type == 'resource') {
                $class .= ' type-resource';
            }

            // 5. Menu item Y/N
            if ($page->menu_item) {
                $class .= ' menu-item';
            }

            // 6. Modules
            //    Modules conceptually started out life as just a rule:
            //    SHOULD BE parent_id = 0 AND menu_item = true, type = 'module'
            //    But that has been replaced with a page type of module.
            if ($page->type == 'module') {
                $class .= ' type-module';
            }

            /* this was the rule before adding the page type of module.
            // 6. Is module? (parent_id is empty, has children)
            //if ($page->type == 'module') {
            if (empty($page->parent_id) && !empty($page->children)) {
                $class .= ' type-module';
            }
            */

            // 7. Is sub-menu? (parent_id > 0, has children)
            if ($page->type == 'submenu') {
            //if ($page->parent_id > 0 && !empty($page->children)) {    // DEPRECATED - added back in the page type of submenu
                $class .= ' type-submenu';
            }

            // Oops. Didn't match anything?
            if ($class == '') {
                $class = 'type-unidentified';
            }

            $page->display_class = $class;

        }

        return $input_pages;
    }


    /**
     * Which Module is at the top of my pages tree?
     * Probably only used by the ehPagesController to display the Module-Tree sidebar.
     *
     * @param int|object $id           // Accepts a page id number or a page object.
     * @return object    $tmp
     */

    static function getMyModule($id)
    {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Check for a 0 or null being passed.
        if (empty($id)) {
            return collect([]);
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Normalize the page id to an object.
        $id = ehPage::normalizePageID($id);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Then get the parent module for this child.
        $tmp = self::getMyParent($id->id);


        // Assuming, that if there was no parent id, then
        // -- this must be either the base Module, a root menu/script item or an orphan?
        // -- (We'll have to protect for orphans in the ehPagesController@dataConsistency checks and deletion rules.)
        if ($tmp->count() == 0) {
            return collect([]);
        }

        // Only one parent id returned so use index [0].
        // Loop and pull parents until we get to the main Module
        //  (or passed id may be a non-module, root-level menu item).
        while ($tmp[0]->parent_id > 0) {
            $tmp = self::getMyParent($tmp[0]->parent_id);
        }

        return $tmp[0];

    }


    /**
     * Internal used for getMyModule().
     * Quick helper to just get the whole $page record associated with a single $id number.
     *
     * @param $parent_id
     * @return mixed
     */
    protected static function getMyParent($parent_id)
    {
        // Return the Eloquent Collection as a Support Collection
        return collect(ehPage::where('id', $parent_id)->get());
    }


    /**
     * Return a collection of all pages that list this page_id as their parent.
     * This can be used for a couple different things based on exactly what is needed.
     * It defaults to including everything but you can limit it to just active items and just menu items as needed.
     *
     * @param $page_id
     * @param $active_only      // true = Only return active items.
     *  @param $menu_itme       // true = Only return menu items.
     * @return mixed
     */
    public static function getMyChildren($page_id, $active_only = false, $menu_item = false) {

        // If we passed the whole $page object then just grab the id.
        // Otherwise we assume that it is just a page id number.
        if (is_object($page_id)) {
            $page_id = $page_id->id;
        }

        // Build out the part of the query to deal with any passed parameters
        if ($active_only) {
            $active_only = [['active','=',1]];
        } else {
            //$active_only = [];
        }
        if ($menu_item) {
            $menu_item = [['menu_item','=',1]];
        } else {
            //$menu_item = [];
        }

        //return collect(ehPage::where('parent_id', $page_id)->get());
        /*
        $result = collect(
            ehPage::where([
                ['parent_id', '=', $m->parent_id],
                ['menu_item', '=', 1],
                ['active', '=', 1]
            ])
            ->orderBy('order')
            ->get()
        );
        */


        // Build the whole query based on the arrays above.
        $whole_query = [
            ['parent_id', '=', $page_id],
        ];
        if (!empty($active_only)) {
            $whole_query = array_merge($whole_query, $active_only);
        }
        if (!empty($menu_item)) {
            $whole_query = array_merge($whole_query, $menu_item);
        }


        // Return the Eloquent Collection as a Support Collection
        return collect(
            ehPage::where(
               $whole_query             // Where clause with multiple parts built out above here.
            )
                ->orderBy('order')
                ->get()
        );


    }


    /**
     * Returns the array containing the Menus/ Pages List "Legend" for use on the Menus/Pages index page.
     * Note: the display_class is also added for each item entry.
     *
     * The data itself is below in the legendData() method.
     *
     * @return array
     */
    public function getLegend()
    {

        // Get the data defined below.
        $tmp = $this->legendData();

        // Add the display classes for each page item.
        $tmp = self::addDisplayClass($tmp);

        // Return the legend data with display classes.
        return $tmp;
    }


    /**
     * Data that is used exclusively to draw the Menus/Pages List "Legend".
     * Note: the specific use of "collect" and "object" is to get this data to coexist with the methods intended to be used with returned database data.
     *
     * @return \string[][]
     */
    protected function legendData()
    {
        return collect([
            (object)['id' => '1', 'name' => 'Page with no menu item and no module.', 'alt_text' => '', 'description' => '', 'type' => 'page', 'active' => '1', 'security' => '', 'icon' => '', 'parent_id' => '0', 'menu_item' => '0', 'order' => '1', 'route' => 'test-1', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                'children'=>[]],
            (object)['id' => '2', 'name' => 'Inactive page with no menu item and no module.', 'alt_text' => '', 'description' => '', 'type' => 'page', 'active' => '0', 'security' => '', 'icon' => '', 'parent_id' => '0', 'menu_item' => '0', 'order' => '2', 'route' => 'test-2', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                'children'=>[]],

            (object)['id' => '3', 'name' => 'Module 1 (active)', 'alt_text' => '', 'description' => '', 'type' => 'module', 'active' => '1', 'security' => '', 'icon' => '', 'parent_id' => '0', 'menu_item' => '1', 'order' => '3', 'route' => 'test-3', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                'children'=>[
                    (object)['id' => '5', 'name' => 'Inactive Page with no menu item', 'alt_text' => '', 'description' => '', 'type' => 'page', 'active' => '0', 'security' => '', 'icon' => '', 'parent_id' => '3', 'menu_item' => '0', 'order' => '5', 'route' => 'test-5', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                        'children'=>[]],
                    (object)['id' => '6', 'name' => 'Method call (script) only.', 'alt_text' => '', 'description' => '', 'type' => 'method', 'active' => '1', 'security' => '', 'icon' => '', 'parent_id' => '3', 'menu_item' => '0', 'order' => '6', 'route' => 'test-6', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                        'children'=>[]],
                    (object)['id' => '8', 'name' => 'Active Menu Item for a resource route page.', 'alt_text' => '', 'description' => '', 'type' => 'resource', 'active' => '1', 'security' => '', 'icon' => '', 'parent_id' => '3', 'menu_item' => '1', 'order' => '8', 'route' => 'test-8', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                        'children'=>[]],
                    (object)['id' => '4', 'name' => 'Page with no menu item', 'alt_text' => '', 'description' => '', 'type' => 'page', 'active' => '1', 'security' => '', 'icon' => '', 'parent_id' => '3', 'menu_item' => '0', 'order' => '4', 'route' => 'test-4', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                        'children'=>[]],
                    (object)['id' => '7', 'name' => 'Inactive Method call (script) only.', 'alt_text' => '', 'description' => '', 'type' => 'method', 'active' => '0', 'security' => '', 'icon' => '', 'parent_id' => '3', 'menu_item' => '0', 'order' => '7', 'route' => 'test-7', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                        'children'=>[]],
                    (object)['id' => '9', 'name' => 'Active Menu Item for a single route page.', 'alt_text' => '', 'description' => '', 'type' => 'page', 'active' => '1', 'security' => '', 'icon' => '', 'parent_id' => '3', 'menu_item' => '1', 'order' => '9', 'route' => 'test-9', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                        'children'=>[]],
                    (object)['id' => '10', 'name' => 'Inactive Menu Item page.', 'alt_text' => '', 'description' => '', 'type' => 'page', 'active' => '0', 'security' => '', 'icon' => '', 'parent_id' => '3', 'menu_item' => '1', 'order' => '10', 'route' => 'test-10', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                        'children'=>[]],
                    (object)['id' => '11', 'name' => 'Active Submenu.', 'alt_text' => '', 'description' => '', 'type' => 'page', 'active' => '1', 'security' => '', 'icon' => '', 'parent_id' => '3', 'menu_item' => '1', 'order' => '11', 'route' => 'test-9', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                        'children'=>[
                            (object)['id' => '12', 'name' => 'Active Submenu Menu Item page.', 'alt_text' => '', 'description' => '', 'type' => 'page', 'active' => '1', 'security' => '', 'icon' => '', 'parent_id' => '11', 'menu_item' => '1', 'order' => '12', 'route' => 'test-10', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                                'children'=>[]],
                            (object)['id' => '13', 'name' => 'Inactive Submenu Menu Item page.', 'alt_text' => '', 'description' => '', 'type' => 'page', 'active' => '0', 'security' => '', 'icon' => '', 'parent_id' => '11', 'menu_item' => '1', 'order' => '12', 'route' => 'test-10', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                                'children'=>[]],
                        ]],

                ]
            ],

            (object)['id' => '13', 'name' => 'Module 2 (inactive)', 'alt_text' => '', 'description' => '', 'type' => 'module', 'active' => '', 'security' => '', 'icon' => '', 'parent_id' => '0', 'menu_item' => '1', 'order' => '11', 'route' => 'test-11', 'feature_1' => '', 'feature_2' => '', 'feature_3' => '', 'feature_4' => '', 'comment' => '',
                'children'=>[]],
        ]);
    }


    /**
     * For internal use in building out the menu data to return.
     * Checks both the non-user page level security along with the actual user role permissions for this route.
     *
     * IMPORTANT NOTE:
     * This is a "front-end" security check only. All it does it keep the menu item from showing if you don't have access.
     * Back-end permission checks through the permissions middleware will handle the actual back-end lock-out security.
     *
     * @param $page
     * @return bool|void
     */
    protected static function pageSecurity($page)
    {

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 1. Is the logged in user an admin?
        if (!Auth()->guest()) {
            if (Auth()->user()->isAdmin()) {
                // If User is a Site Admin, then they get everything (God access). -- skip all subsequent tests.
                return true;
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 2. Check the "Page Level" access.
        /*
        * Remember each page has these access rights now:
        *  0- No access
        *  1- Public
        *  2- Authenticated
        *  3- Permissions Check
        */

        // Page security level is "No access". No one can access page.
        // Note: validation rules should keep this from being blank but--who knows--check here to be safe.
        if ($page->security == 0 or empty($page->security)) {
            return false;
        }

        // Page security level is "Public". Anyone can access this page whether they're logged in or not.
        if ($page->security == 1) {
            return true;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 3. Are we logged in?
        // Subsequent checks are for authenticated users only so punch out if user is not.
        if (Auth()->guest()) {
            return false;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 4. Does the page level specify that you just needs to be logged in?
        // Page security level is 2-"Authenticated". Anyone logged in can access.
        // Step #3 above already confirmed that the user is in fact logged in.
        if ($page->security == 2) {
            return true;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 5. Acting Role error check.
        // Make sure we have an "acting role" or default group set before continuing.
        if (empty(ehUser::getActingRole())) {
            return false;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 6. Actual permission rights check.
        //    Note: If we got here, we know the user is logged in.
        //    Note: For menu building, we're only checking to see if the user can "see" (view) this item.
        //    !! IMPORTANT !! - This IS NOT PART OF THE BACK-END SECURITY CHECK.
        //                      THAT IS HANDLED THROUGH THE check_permissions MIDDLEWARE.
        //                    - ehMenus only controls what shows up on the menu tree -- not the "access" to it.

        if (ehAccess::getUserRights(Auth()->user()->id, $page->id)->view) {
            return true;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 7. If we dropped through to here, then the user has failed all other checks for this menu item, so no-go.
        return false;

    }


    /**
     * Returns the final menus/pages collection that was built out in __construct.
     * This is a hierarchical set of data that includes all children and display classes.
     * __construct will pull a set for either 'all' or 'user' based on the passed parameter.
     *
     * @return mixed
     */
    public function getPages()
    {
        return collect($this->pages_output);
    }

    /**
     * Returns the menu/pages records but flattens out the children level so ALL subsequent children are directly under the parent.
     * Basically a single-dimensional list of ALL children under this module (and any submenus) at any level.
     * SECURITY NOTE: There is no security check since this is only used by the ehRolesController rights grid !!
     * This works directly with recurseMyFlatChildren() below.
     * Currently, this is only used for the GroupController rights grid (but leaving it here in case other use cases arise).
     *
     * @return array
     */
    public function getFlatPages() {

        // Note: the first level items that come directly from $this->>page_output will have the $display_class property.
        // The recurseMyFlatChildren() is responsible for adding them to its data before returning.

        $output = [];

        foreach($this->pages_output as $item) {

            unset($item->children);
            $output[] = $item;      // Keep the one we're on then check it for children.

            // Add the current output together with any children levels for this id.
            $children_levels = $this->recurseMyFlatChildren($item->id);
            if (!empty($children_levels)) {
                $output = array_merge($output, $children_levels);
            }

        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Note: Even with the above recursion call, this array will be a flat/ single-dimensional array.
        return $output;

    }

    /**
     * Internal use for the getFlatPages() method above.
     * SECURITY NOTE: There is no security check since this is only used by the ehRolesController rights grid !!
     *
     * @param $start_id
     * @return array
     */
    protected function recurseMyFlatChildren($start_id){

        // Note: the first level items that come directly from $this->>page_output will have the $display_class property.
        // The recurseMyFlatChildren() is responsible for adding them to its data before returning.

        $all_children = [];

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Pull a list of child entries for this start_id
        $query = "SELECT * FROM eh_pages WHERE parent_id = {$start_id}" . $this->menu_filter . $this->order_by .";";
        $tmp = DB::select($query);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Call recursively until there are no more downstream children for this start_id.
        foreach ($tmp as $child) {

            $all_children[] = $child;           // Just add this child w/o regard for the security level.
            if (!empty($child->children)) {     // Does this item have any children of its own?
                $all_children = self::recurseMyChildren($child->id);
            }

        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Since this data is the result of a fresh new query, we'll need to add the display class here.
        $all_children = $this->addDisplayClass($all_children);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Note: Even with recursion, this array will be a flat/ single-dimensional array.
        return $all_children;   // Return the $children array for "merging" into the parent items recordset

    }




}
