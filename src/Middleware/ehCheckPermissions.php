<?php

//namespace App\Http\Middleware;
namespace ScottNason\EcoHelpers\Middleware;

use App\Classes\Access;
use App\Menu;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Closure;

use ScottNason\EcoHelpers\Classes\ehAccess;
use ScottNason\EcoHelpers\Classes\ehConfig;
use ScottNason\EcoHelpers\Classes\ehMenus;
use ScottNason\EcoHelpers\Models\ehPage;


/**
 * The basic middleware('auth') checks only to see if you're logged in.
 * This checks the logged in user's assigned permissions by acting role -- to this page/route.
 *
 * This middleware's job is to determine, "If the user can access this page/route."
 *
 * This is registered (by the package discovery??) in the App/http/Kernel file under:
 *  protected $routeMiddleware = [
 *   'check_permissions' => \ScottNason\EcoHelpers\Middleware\ehCheckPermissions::class
 *
 * This is the main ecoFramework base route permissions check for all controllers that extend Controller.
 *  (added in the base Controller after the 'auth' middleware check.)
 *
 * !! WARNING -- if your controller does not extend Controller and you need this security
 *  - just add the middleware 'check_permissions' after 'web' (see the ehBaseController for an example)
 *
 * Checks for a fully qualified route name like: examples.index
 *  Then we'll look for page type = 'resource'
 *  and then check the associated user permission for the requested action
 *  index, show, create, edit, destroy, store, update
 *
 *  NOTE: By design, each page/route requires an entry in  "pages" and then
 *        applied permissions in a role the user has assigned.
 *        Without an entry in pages--or permissions applied to a role, the system defaults to NO ACCESS.
 *        In this way, forgetting to do something results in NO ACCESS rather than the other way around.
 */
class ehCheckPermissions
{
    /**
     * Prepended to the front of any message generated as a result of a permissions failure.
     * Helps to identify exactly where the error came from.
     *
     * @var string
     */
    protected $error_prefix = "Check Permission: ";
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed|null
     */
    public function handle($request, Closure $next)
    {

        ////////////////////////////////////////////////////////////////////////////////////////////
        // NOTE: The Global Middleware (before Controller) stack runs prior to route dispatch or login auth.
        //       so there is NO ACCESS to Auth() or Route() then.
        //       So, it looks like when you add the middleware to the Kernel.php file (and in the ehBaseController __construct.)
        //       - then we DO HAVE ACCESS to both Auth() and Route().
        ////////////////////////////////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Make sure and start out with no access.
        $i_can_access = false;

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 0.a. IS THE ACCESS SYSTEM ENABLED?
        // Before we do anything let's check to see if we even have the permissions/access system enabled.
        // Note: ehBaseModel has a similar check too.
        //TODO: Determine if this is even needed here. The way ehBaseController adds in this middleware,
        // is only after it check for the access.enabled flag.
        if (ehConfig::get('access.enabled') != true) {
            return $next($request);     // Then just handoff to the next middleware.
            //return true;    // if the security and permissions system is not enabled then just return access = true.
        }
        ///////////////////////////////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 0b. Skip this middleware for any of the auth routes defined in the config file.
//dd(ehConfig::get('access.auth_routes'));


        ////////////////////////////////////////////////////////////////////////////////////////////
        // 0c. And before we do anything else we have to check and see if this user is an Admin -- then skip everything else.
        if (!Auth()->guest()) {

            ////////////////////////////////////////////////////////////////////////////////////////////
            // 0. Is this user a site admin?
            // If so, then just skip all the security checks and handoff to the next middleware.
            if (Auth()->user()->isAdmin()) {
                return $next($request);     // Then just handoff to the next middleware.
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 1. First let's get the route name and then see if we have a page entry.
        // (Route is available here since this is run in the Controller __construct.)

        $route_name = Route::currentRouteName();
        // When doing an ajax call (like ContactsController@find()) the Route;;currentRouteName() is null
        //  so you have to get it this way (for some reason):
        if ($route_name == null) {
            $route_name = $request->route()->getCompiled()->getStaticPrefix();
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 2. Check the pages table for an entry.
        $page = ehPage::getPageInfo($route_name);

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 3. Do we have a page entry?
        // IF NOT, WE HAVE TO ASSUME NO ACCESS !
        // This is a basic design tenant - no page entry - NO ACCESS.
        // You must actively specify access otherwise it will always be assumed to be none!
        if (!$page) {
            // Abort and go to the fail message
            return $this->throwPermissionError('No page entry');
        }

        // NOTE: The pages table stores the "security" field for each route.
        //  0=>'0-No Access',
        //  1=>'1-Public Access',
        //  2=>'2-Authenticated Only',
        //  3=>'3-Full Permissions Check',

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 4. Does the page entry have a security level greater than 0-No Access?
        if ($page->security == 0) {
            // Abort and go to the fail message.
            return $this->throwPermissionError('Page entry has no access.');
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 5. Is this a public page (and active?)
        if ($page->security == 1 && $page->active == 1) {

            // Is the system configured to allow unauthenticated children?
            // Or does it require those to follow the settings of their parent.
            // If it's true; then we're good to go and will pass the access request.
            // But if it's false; then we need to check the permissions on the parent module.

            if (!ehConfig::get('access.allow_unauthenticated_children')) {

                // allow_unauthenticated_children = false

                // But, if it's false, we need to pull the parent module and see how it's set.
                $m = ehMenus::getMyModule($page->id);

                // Once again - Is this a public module (and active?)
                if ($m->security == 1 && $m->active == 1) {

                    // Access permission check passes.
                    // This is an active, public module with a system setting of allow_unauthenticated_children.
                    // Then just handoff to the next middleware.
                    return $next($request);

                } else {

                    // Looks like the parent module is not a public page so,
                    // We need to check the actual user's access rights to it.

                    // 0- no access. (module)   -- but first lets make sure it's not set to no access at all.
                    if ($m->security == 0) {
                        // Abort and go to the fail message.
                        return $this->throwPermissionError('Parent module entry has no access.');
                    }

                    // At this point we're going to duplicate a bunch of code below here --
                    // unless we can find someway to effectively package it up into little functions.
                    // But the "returns" get wierd that way, so just duplicating for now.

                    // 2- auth only. (module)
                    if ($m->security == 2) {
                        if (Auth()->guest()) {
                            // Force a login page.
                            return redirect(config('app.url').'/login');

                            // Return back to intended and force a login popup
                            //$request->forceLogin=1;
                            //return redirect(Session::get('url.intended'))->withInput($request->all());

                        } else {
                            // User is logged in.
                            // So, the access permission check passes.
                            // Then just handoff to the next middleware.
                            return $next($request);
                        }
                    }

                    // 3- full permissions check. (module)
                    if ($m->security == 3) {
                        if (Auth()->guest()) {
                            // Force a login page.
                            return redirect(config('app.url').'/login');

                            // Return back to intended and force a login popup
                            //$request->forceLogin=1;
                            //return redirect(Session::get('url.intended'))->withInput($request->all());

                        } else {
                            // User is logged in.

                            //////////////////////////////////////////////////////////////////////////////////////////
                            // Check for basic (VIEW) route access here for the parent module.
                            if(ehAccess::chkUserResourceAccess(Auth::user(), $m->id, ACCESS_VIEW)) {
                                // So, the access permission check passes.
                                // Then just handoff to the next middleware.
                                return $next($request);
                            } else {
                                // User does not have view permissions on this module - so too bad.
                                // Pass the error message to the exception handler.
                                return $this->throwPermissionError('Insufficient permissions to access this resource.');
                            }
                        }
                    }
                    dd('the module dropped through all access checks', $m->security);

                }

            } else {
                // allow_unauthenticated_children = true

                // This is an active, public page with a system setting of allow_unauthenticated_children.
                // So, the access permission check passes.
                // Then just handoff to the next middleware.
                return $next($request);
            }




        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 6. Page is just not active.
        if ($page->active == 0) {
            // Abort and go to the fail message.
            return $this->throwPermissionError('This page entry is not active.');
        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 7. So at this point we need to know if the user is logged in.
        if (Auth()->guest()) {
            // If not; since we already checked the route for 0-no access or 1-public access --
            //         we have to know the page requires some access check.

            // Use a login route
            return redirect(config('app.url').'/login');     // So just force a login page.

            // Return back to intended and force a login popup
            //$request->forceLogin=1;
            //return redirect(Session::get('url.intended'))->withInput($request->all());


        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        // If user IS logged in; then we can check:
        // - the user's force password reset flag
        // - check the route (page) information and check for 2-auth only, 3-full permission check.

        ////////////////////////////////////////////////////////////////////////////////////////////
        // 8. FORCE PASSWORD RESET
        // Did the user previously have a force password set but skipped it?
        // (if so, sorry -- we can't let you through)
        if (Auth()->check()) {
            if (Auth()->user()->uforcepwreset == 1) {
                // TODO: This will have to be a modal popup mechanism. (?).
                return redirect(config('app.url').'/password/reset');
            }
        }


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Otherwise -- if this is NOT a 0-no access or 1-public page, it must be Protected.
        // So continue on to check the logged in user's access to the requested route (uri and method).
        ////////////////////////////////////////////////////////////////////////////////////////////


        ////////////////////////////////////////////////////////////////////////////////////////////
        // 9. Is the page security 2-authenticated only?
        //    We already know that we're logged in if we got here so just move on.
        //    We also know that we previously checked if the page was active or not.
        if ($page->security == 2) {
            // Permission check passes (user is logged in and page is active).
            // Then just handoff to the next middleware.
            return $next($request);
        }


        ////////////////////////////////////////////////////////////////////////////////////////////
        // 10. Is this route a resource or something else?
        if ($page->type == 'resource') {
            //////////////////////////////////////////////////////////////////////////////////////////
            // 10a. THIS IS A RESOURCE ROUTE.
            // If it's a resource route we'll have to check the method call and map those to our permissions
            // !! WARNING !! - This is a safety check for convenience here but not all inclusive.
            //                 Other routes can still perform these functions and it is the responsibility of each controller
            //                  to ensure the back-end security is applied properly.

            /*
             * Resource routes map to these permissions (or rights):<br/>
             *  index   -   GET/HEAD    ACCESS_VIEW<br/>
             *  store   -   POST        ACCESS_ADD<br/>
             *  create  -   GET/HEAD    ACCESS_ADD<br/>
             *  show    -   GET/HEAD    ACCESS_VIEW<br/>
             *  update  -   PUT/PATCH   ACCESS_EDIT<br/>
             *  destroy -   DELETE      ACCESS_DELETE<br/>
             *  edit    -   GET/HEAD    ACCESS_EDIT<br/>
             * */

            // Check the route name for each of the resourceful route methods.
            switch ($route_name) {
                case str_contains($route_name,'index'):
                    $i_can_access = ehAccess::chkUserResourceAccess(Auth::user(), $route_name, ACCESS_VIEW);
                    break;
                case str_contains($route_name,'store'):
                    $i_can_access = ehAccess::chkUserResourceAccess(Auth::user(), $route_name, ACCESS_ADD);
                    break;
                case str_contains($route_name,'create'):
                    $i_can_access = ehAccess::chkUserResourceAccess(Auth::user(), $route_name, ACCESS_ADD);
                    break;
                case str_contains($route_name,'show'):
                    $i_can_access = ehAccess::chkUserResourceAccess(Auth::user(), $route_name, ACCESS_VIEW);
                    break;
                case str_contains($route_name,'update'):
                    $i_can_access = ehAccess::chkUserResourceAccess(Auth::user(), $route_name, ACCESS_EDIT);
                    break;
                case str_contains($route_name,'destroy'):
                    $i_can_access = ehAccess::chkUserResourceAccess(Auth::user(), $route_name, ACCESS_DELETE);
                    break;
                case str_contains($route_name,'edit'):
                    $i_can_access = ehAccess::chkUserResourceAccess(Auth::user(), $route_name, ACCESS_EDIT);
                    break;
                default:
                    // If this is truly a resource route it should match one of those.
                    //TODO: does this mean the controller is responsible and we should allow the access here?
                    // Or not allow access?? (does that create a situation where any other method on a resourceful controller is always blocked?
                    // For now, just do nothing.
                    //$i_can_access = ??;
            }

        } else {
            //////////////////////////////////////////////////////////////////////////////////////////
            // 10b. THIS IS NOT A RESOURCE ROUTE.
            // If this is not a resource route, then just check for the user's
            // basic (VIEW) route access here.
            $i_can_access = ehAccess::chkUserResourceAccess(Auth::user(), $route_name, ACCESS_VIEW);
        }


        ////////////////////////////////////////////////////////////////////////////////////////////
        // 11.If you don't have access then display the message or redirect to home.
        if (!$i_can_access) {

            // Pass the error message to the exception handler.
            return $this->throwPermissionError('Insufficient permissions to access this resource.');

        }

        ////////////////////////////////////////////////////////////////////////////////////////////
        return $next($request);     // Hand off to the next middleware.
    }



    /**
     * Decide whether to abort execution with a verbose permissions error message
     * or to take a more secure approach of just redirecting to the home page.
     * This check uses the config('app.debug') flag to decide.
     *
     * @param $error_content
     * @return void
     */
    protected function throwPermissionError($error_content) {

        //TODO: think about beefing up the error message with a little more information like we had before.
        // But remember if we're going to use user() anything have to check if logged in first.
        /*
            if (Auth()->check()) {
                abort(403, 'Insufficient permissions on '.$route_name.': usid:'.
                    Auth()->user()->usID.', role:'.Auth()->user()->getActingRole().'  (Live site will redirect to home)');
            } else {
                abort(403, 'Insufficient permissions on '.$route_name.': usid:'.
                    '-not logged in-'.', ugsid:'.'-not logged in-'.'  (Live site will redirect to home)');
            }
        */

        /*  Don't know if there's an opportunity here or not to provide a better ajax error mechanism?
            Maybe add a json encode?
            // Check to see if this is a method call
            //  Attempting to give more usable information on an Ajax permissions fail (very hard to trouble shoot.)
            $p = ehPage::getPageInfo($route_name);
            if (isset($p->type) && $p->type=='method') {
                abort(403,'CheckPermissions: Method permission restriction issue.');
            }
         */


        // Check the app status for debug true or false.
        // If false then just silently send users back to the home page for any permission access constraint.
        if (config('app.debug')) {
            abort(403, $this->error_prefix . $error_content);
        } else {
            // TODO: can we log this error somehow (by throwing an exception or something?)
            // Remember, for the redirect to work the calling line has to return $this->throwPermissionError()
            return redirect('/');
        }

    }


}




