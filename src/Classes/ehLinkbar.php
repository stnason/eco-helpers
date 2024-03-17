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
 *    'items_array' =>
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
    protected array  $items_array = [
        /* This is the format for the content, but including this in the definition leaves a blank first array item.
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
        
        if ($auto_generate) {
            // Pull the default link bar information from the parent module's items.
            $this->buildParentModuleLinkArray();

            // Then add the Export All link when appropriate (it's not turned off and the user has permissions).
            $this->addExportItem();
        }

    }


    /**
     * Build out and return an array of linkbar items based on the current route's parent module.
     *
     * Note: This does check user permissions to see if a particular route/page should be included.
     *
     * @return bool
     */
    protected function buildParentModuleLinkArray() {

        // 1. Get the current page by route name.
        $m = ehPage::getPageInfo();

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

                // If we passed all the tests above then include this in the output array.
                if ($okay) {
                    $this->items_array[] =
                        [
                            'href'=>url($item->route),
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
     *
     * @return void
     */
    protected function addExportItem()
    {
        ////////////////////////////////////////////////////////////////////////////////////////////

        // Are we turning off Export for this page?
        if (!$this->hide_export_all) {

            // Does the current logged in user have EXPORT TABLE rights?
            if (ehAccess::chkUserResourceAccess(null, null, ACCESS_EXPORT_TABLE))
            {
                if (empty($this->export_table_name)) {

                    // We're assuming the table_name is the name of the base route
                    // so extract that here.
                    $r = explode('.', Route::currentRouteName());

                    $export_table = '';
                    if (!empty($r[0])) {
                        $export_table = $r[0];
                    }

                } else {
                    // Otherwise, use the provided export table name
                    $export_table = $this->export_table_name;
                }

                ////////////////////////////////////////////////////////////////////////////////////////////
                // Looks like we're not explicitly hiding the Export ALL link
                // and the user has appropriate permissions so go ahead and add this item.
                /*
                $output .= '&nbsp;|&nbsp;<li><a href="' .
                    config('app.url') . '/export/' . $export_table . '">' . 'Export All' .
                    '</a></li>' . config('app.nl');
                */
                $this->items_array[] =
                    [
                        'href'=>url(config('app.url') . '/export/' . $export_table),
                        'name'=>'Export All',
                        'title'=>'Export the table for this page.',
                        'target'=>'_self'
                    ];
            }
        }

    }



    /**
     * Return the completed linkbar array for display.
     *
     * @return array|array[]
     */
    public function getLinkbar() {
        return $this->items_array;
    }

    public function setExportTableName($export_table_name) {
        $this->export_table_name = $export_table_name;
    }
    /**
     * Export All already checks to see if you have rights; but additionally can be toggled off here.
     * @param bool $hide_export_all
     */
    public function setHideExportAll($hide_export_all = true) {
        $this->hide_export_all = $hide_export_all;
    }







}
