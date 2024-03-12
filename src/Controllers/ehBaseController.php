<?php

namespace ScottNason\EcoHelpers\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use ScottNason\EcoHelpers\Classes\ehConfig;
use ScottNason\EcoHelpers\Classes\ehMenus;
use ScottNason\EcoHelpers\Models\ehPage;

/**
 * ehBaseController provides the core package hooks into the permissions checking middleware
 * and all other Controllers must extend it.
 *
 */
class ehBaseController extends BaseController
{

    // Things that we will potentially need for every page.
    protected $form = [];               // To pass any page data to the Blade View.
    protected $access_token = 0;        // Holds the user's access token for the current route.


    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * Create a new controller instance.
     * Including this here since all pages will be security controlled by default.
     *
     * @return void
     */
    public function __construct()
    {

        // Force every page that extends this controller to check for authentication requirements.
        // If a page should not be authenticated, you can override the __construct() and just use 'web' only.
        // The Permissions middleware will check the Pages table to see if this route is set to "public"

        $this->middleware('web');               // This is needed if you want to use a session,
                                                // w/o it, $errors variable is missing for the base template.

        ///////////////////////////////////////////////////////////////////////////////////////////
        // IS THE ACCESS SYSTEM ENABLED?
        // Before we do anything let's check to see if we even have the permissions/access system enabled.
        // Note: ehCheckPermissions has a similar check too.
        if (ehConfig::get('access.enabled') === true) {

            ///////////////////////////////////////////////////////////////////////////////////////////
            // Check to see if this route is requires authentication
            // Note: This IS NOT the complete permissions check.
            // Only checking to see if we need to include the 'auth' middleware here. That will force a login if user is not already.
            $p = ehPage::getPageInfo();
            if ($p) {
                if ($p->security > 1) {                             // 0-no access, 1-public, 2-authenticated, 3-full access
                    $this->middleware(['auth', 'verified']);        // This is needed to check for an authenticated/verified user and redirect to login if not.
                }
            }

            // And check the allow_unauthenticated_children flag and then the module security too.
            // If we're not allowing authenticated children then we need to check security setting of the parent module.
            if (!ehConfig::get('access.allow_unauthenticated_children')) {
                $m = ehMenus::getMyModule($p);

                if (isset($m->security)) {
                    if ($m->security > 1)                           // 0-no access, 1-public, 2-authenticated, 3-full access
                        $this->middleware(['auth', 'verified']);    // This is needed to check for an authenticated/verified user and redirect to login if not.
                }
            }

            // Leave this outside of the above if to make the final determination for access.
            $this->middleware('check_permissions');     // ecoHelpers custom (granular/ token-based) permissions check.


            // It looks like we may not need to do anything at all.
            // The user agent may just take care of this when using UTC timestamps.
            // Leaving this here just in case something comes up later.
            // 1.Set system to the user's timezone (if set in their profile)
            // This was in the ehAuthenticatedSessionController but was not persistent.
            // UTC is set in the app.php by default.
            //if (!empty(Auth()->user()->timezone)) {
            //    date_default_timezone_set(Auth()->user()->timezone);
            //}

        }
    }


    /**
     * A simple check to ensure that subsequent page controllers have been setup to extend this one.
     * This function literally does nothing; except ehLayout check to see if it exist() to make
     * sure this controller instance has extended ehBaseController.
     *
     * @return bool|mixed
     */
    public function doesExtendBaseController() {
        return null;
    }



}
