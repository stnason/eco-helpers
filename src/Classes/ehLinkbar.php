<?php

namespace ScottNason\EcoHelpers\Classes;

use ScottNason\EcoHelpers\Models\ehPage;
use Illuminate\Support\Facades\Route;

/**
 * ehLinkbar creates links to pages, available for display in the Linkbar area of the master app template.
 * ehLinkbar can automatically create links for all of the child application routes underneath a given menu item.
 * Note: all items are checked for permissions before adding them to the final output array.
 *
 * Also can provide an Export All link for access to underlying table with appropriate role access permissions.''
 *
 * REMEMBER TO KEEP CHANGES TO THE ARRAY FORMAT IN SYNC WITH THE eco-helpers.php config file and the base template.
 *    'internal_items' =>
 *       [
 * ['href'=>'https://nasonproductions.com', 'name'=>'np.com', 'title'=>'link to np.com', 'target'=>'_blank'],
 * ['href'=>'https://nasonproductions.com', 'name'=>'np.com', 'title'=>'link to np.com', 'target'=>'_blank'],
 * ['href'=>'https://nasonproductions.com', 'name'=>'np.com', 'title'=>'link to np.com', 'target'=>'_blank'],
 * ]
 */
class ehLinkbar {

    /**
     * This is needed in order to use $linkbar->getLinkbar() to instantiate an empty linkbar.
     * Without that, or if you just call ehLayout::setLinkbar(), the defaults will be displayed from the eco-helpers
     * file.
     * @var array|array[]
     */
    protected array  $internal_items = [
        /* This is the format for the content return.
           But, including it in the initial definition creates a blank first array item.
        [
        'href'=>'',
        'name'=>'',
        'title'=>'',
        'target'=>''
        ]
        */
    ];            // An array of linkbar items.
    protected bool   $hide_export_all = false;     // Show the Export all link yes/no.
    protected bool   $show_self = false;           // Normal behavior is to not show a link to the page we're on.
    protected string $export_table_name = '';      // Set the name of the table to be exported. Without it we'll just assume the base route.
    protected string $target = '_self';            // The href target (_blank, _self)
    protected bool $auto_generate = true;          // Normal behavior is to auto generate based on the parent module of this route.

    /**
     * LinkBar constructor.
     *  If $auto_generate is true then we will build a link bar array
     *  based on the current pages parent module.
     * 
     *  If $auto_generate is false, then we will build an empty link bar and
     *  the calling page will have to either add individual routes or pass a complete array.
     *
     * @param bool $auto_generate
     */
    public function __construct($auto_generate = true)
    {
        $this->auto_generate = $auto_generate;

        if ($auto_generate) {
            // Pull the default link bar information from the parent module's items.
            $this->buildParentModuleLinkArray();

            // Do this at the end with calling getLinkbar().
            // Then add the Export All link when appropriate (it's not turned off and the user has permissions).
            // $this->addExportAllLink();
        }


    }


    /**
     * Build out an array of linkbar items based on the current route's parent module.
     * Note: This DOES check user permissions to see if a particular route/page should be included.
     *
     * @return bool
     */
    protected function buildParentModuleLinkArray() {

        // 1. Get the current page by route name.
        $m = ehPage::getPageInfo();

        // If ehPage did not find and entry, it will return false.
        if(empty($m)) {
            return false;
        }

        // 2. Pull all the children for this parent module that are active and a menu item.
        $result = ehMenus::getMyChildren($m->parent_id, true, true);

        // Loop the result set and check the user's permissions for each one.
        if ($result) {

            foreach($result as $item) {

                // Note: there is no Admin check here. Admin gets the same as everyone else.
                $okay = false;

                // Skip if item is not active or set to 0-no access.
                // Note: we already filtered out non-active in the getMyChildren() call above,
                //       but just leaving it here for clarity and to catch any possible future changes.

                $okay = $this->itemSecurityCheck($item);
                /* Moved this to a separate function toward the bottom (so we can re-use elsewhere as needed).
                if ($item->active && $item->security > 0) {
                    // Check the user's access to this item based on the page's security setting.
                    // 0-no access, 1-public, 2-auth, 3-full permissions
                    if ($item->security == 1) {             // Public
                        $okay = true;
                    } elseif ($item->security == 2) {       // Authenticated
                        $okay = Auth()->check();
                    } elseif ($item->security == 3) {       // Full permissions
                        $okay = ehAccess::chkUserResourceAccess(null, $item->route, ACCESS_VIEW);
                    }
                }
                */

                ////////////////////////////////////////////////////////////////////////////////////////////
                // If we passed all the tests above then include this in the output array.

                // Check route "as is" and see if it doesn't exist - it might be a resource route.
                // Note: similar logic has to take place inside the eh-child-menus.blade.
                $href = '';
                if (Route::has($item->route)) {
                    $href = route($item->route);    // This is actually a defined route name so use as a "route"
                } else {
                    $href = url($item->route);      // Hopefully this is a resource route on not missing altogether.
                }

                // Build out the individual linkbar item.
                if ($okay) {
                    $this->internal_items[] =
                        [
                            'href'=>$href,          // Figure out if this has a valid route name above.
                            'name'=>$item->name,
                            'title'=>$item->alt_text,
                            'target'=>$this->target
                        ];
                }

            }
        }

        return true;    // Everything most probably went okay.

    }


    /**
     * Check this user's permissions on the current route and add the Export All link if called for.
     * Note: This function assumes that the table name is the same as the base route.
     *       http://site-name/pages = 'pages' table
     *       So, if it's different, the controller must set ehLinkbar::setExportTableName('name').
     *
     * @return array
     */
    protected function addExportAllLink($items_array)
    {
        ////////////////////////////////////////////////////////////////////////////////////////////

        // Are we turning off Export for this page?
        if (!$this->hide_export_all) {

            // Does the current logged in user have EXPORT TABLE rights?
            if (ehAccess::chkUserResourceAccess(null, null, ACCESS_EXPORT_TABLE))
            {
                if (empty($this->export_table_name)) {

                    // We're assuming the table_name is the name of the base route
                    //  so extract that name here.
                    $r = explode('.', Route::currentRouteName());

                    $export_table = '';
                    if (!empty($r[0])) {
                        $export_table = $r[0];
                    }

                } else {
                    // Otherwise, use the provided export table name.
                    $export_table = $this->export_table_name;
                }

                ////////////////////////////////////////////////////////////////////////////////////////////
                // Looks like we're not explicitly hiding the Export ALL link
                // and the user has appropriate permissions so go ahead and add this item.
                /* original eesfm html generated code
                $output .= '&nbsp;|&nbsp;<li><a href="' .
                    config('app.url') . '/export/' . $export_table . '">' . 'Export All' .
                    '</a></li>' . config('app.nl');
                */
                //$this->internal_items[] =
                $items_array[] =
                    [
                        'href'=>url(config('app.url') . '/export/' . $export_table),
                        'name'=>'Export All',
                        'title'=>'Export the table for this page.',
                        'target'=>$this->target
                    ];
            }
        }

        return $items_array;
    }


    /**
     * Manually add and item to the linkbar.
     *
     * for 'href' -- Use a laravel route('name') or a full url (http/https path name)
     *
     * 'internal_items' =>
     * [
     *  'href'=>'https://nasonproductions.com',
     *  'name'=>'np.com',
     *  'title'=>'link to np.com',
     *  'target'=>'_blank',
     * ]
     *
     * @param $item_array
     * @return boolean
     */
    public function addItem($item_array) {

        // Minimal error checking
        // We need href, name and title. (we can set target to _self if it's missing)
        if (!isset($item_array['href'])) {
            return false;
        }
        if (!isset($item_array['name'])) {
            return false;
        }
        if (!isset($item_array['title'])) {
            return false;
        }

        // If we have those 3 things then go ahead and add this item to the internal_items array.
        // (Note: we will security check all items in getLinkbar() below.
        $this->internal_items[] = $item_array;
    }



    public function setExportTableName($export_table_name) {
        $this->export_table_name = $export_table_name;
    }
    /**
     * Export All already checks to see if you have rights; but additionally can be toggled off here.
     * @param bool $hide_export_all
     */
    public function setHideExportAll($hide_export_all = true):void {
        $this->hide_export_all = $hide_export_all;
    }

    /**
     * Check the item to see if this user should see it or not based on their access rights to this page.
     * This is expecting a $page record -- NOT an internal linkbar $item.
     *
     * @param $item
     * @return void
     */
    protected function itemSecurityCheck($item) {

        if ($item->active && $item->security > 0) {
            // Check the user's access to this item based on the page's security setting.
            // 0-no access, 1-public, 2-auth, 3-full permissions
            if ($item->security == 1) {             // Public
                $okay = true;
            } elseif ($item->security == 2) {       // Authenticated
                $okay = Auth()->check();
            } elseif ($item->security == 3) {       // Full permissions
                $okay = ehAccess::chkUserResourceAccess(null, $item->route, ACCESS_VIEW);
            }
        }
        return $okay;
    }


    /**
     * Return the completed linkbar array for display.
     *
     * @return array|array[]
     */
    public function getLinkbar() {

        // Construct the array (note: doing it here instead of __construct
        // since the controller can change things that affect it).
        
        // NOTE: ehLayout is responsible for pulling in all of the default settings -- one of which
        // just happens to be the linkbar defaults.

        /*
        if ($this->auto_generate) {
            // Pull the default link bar information from the parent module's items.
            $this->buildParentModuleLinkArray();

            // Then add the Export All link when appropriate (if it's not turned off and the user has permissions).
            $this->addExportAllLink();
        }
        */

        $items_to_return = [];  // Items to return AFTER the security check


        // Build an array with all the available urls=>uri for this app.
        $route_urls = [];
        foreach (Route::getRoutes() as $route) {
            //$route_urls[] = $route->uri;                  // raw route names only
            //$route_urls[] = url($route->uri);             // full url path to resource
            $route_urls[url($route->uri)] = $route->uri;    // both full url and raw uri
        }


        // Loop the $internal_items and check security
        $add_this_item = false;
        foreach ($this->internal_items as $item) {

            // Check the whole array before returning it.
            // That way we check any Linkbars that may have been completely manually created too.
            // 1) See if the passed href is a valid route in this application

            // 2) If item url matches one of the application route urls then check further
            if ( array_key_exists($item['href'], $route_urls) ) {

                // Then use the uri to get the page information by route name.
                $page_info = ehPage::getPageInfo($route_urls[$item['href']]);

                // See if this route is protected and if this user has permissions to this route.
                if ($this->itemSecurityCheck($page_info)) {
                    $add_this_item = true;
                } else {
                    $add_this_item = false;
                }

            } else {
                
                // Otherwise -- if this is not one of our application routes
                // then just include it.
                $add_this_item = true;
                
            }

            // Build the final linkbar array to return
            if ($add_this_item) {
                $items_to_return[] = $item;
            }

        }



        // Then add the Export All link when appropriate (if it's not turned off and the user has permissions).
        $items_to_return = $this->addExportAllLink($items_to_return);

        //return $this->internal_items;
        return $items_to_return;

    }



}
