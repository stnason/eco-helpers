<?php



/*
 * as of 01/16/2023
 *
 *  REMEMBER TO KEEP CHANGES TO THIS FORMAT IN SYNC WITH THE eco-helpers.php config file and the base template.
 *   'items_array' =>
 *      [
            ['href'=>'https://nasonproductions.com', 'name'=>'np.com', 'title'=>'link to np.com', 'target'=>'_blank'],
            ['href'=>'https://nasonproductions.com', 'name'=>'np.com', 'title'=>'link to np.com', 'target'=>'_blank'],
            ['href'=>'https://nasonproductions.com', 'name'=>'np.com', 'title'=>'link to np.com', 'target'=>'_blank'],
        ]
 */




namespace ScottNason\EcoHelpers\Classes;

use ScottNason\EcoHelpers\Models\ehPage;
use Illuminate\Support\Facades\Route;

/**
 * Class LinkBar
 *
 * Linkbars provide an automated or manual set of links for all of the child application routes underneath a given menu item.
 * The base template provides a quick horizontal display of all the linkbar items.
 * Also can provide an Export All link for access to underlying table when appropriate (decided by the controller).
 * Note: all items are checked for permissions before adding them to the final output array.
 *
 */
class ehLinkbar {


    /**
     * This is needed in order to use $linkbar->getLinkbar() to instantiate an empty linkbar.
     * Without that, or if you just call ehLayout::setLinkbar(), the defaults will be displayed from the eco-helpers file.
     * @var array|array[]
     */
    protected array  $items_array = [
        [
        'href'=>'',
        'name'=>'',
        'title'=>'',
        'target'=>''
        ]
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
        }

    }


    /**
     * Build out and return an array of linkbar items based on the current route's parent module.
     *
     * Note: For now, there is no Admin check for permissions. Admin gets the same as everyone else.
     *       After all, it's more of a system behavior building out the automatic linkbar than anything the
     *       Administrator might need to "manage." Just need to be aware that this is the expected behavior.
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


    public function getLinkbar() {
        return $this->items_array;
    }


}
