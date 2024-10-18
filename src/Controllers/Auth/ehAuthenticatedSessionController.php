<?php

namespace ScottNason\EcoHelpers\Controllers\Auth;

use ScottNason\EcoHelpers\Classes\ehConfig;
use ScottNason\EcoHelpers\Models\ehRole;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ScottNason\EcoHelpers\Models\ehRoleLookup;


/**
 * Designed to be the extended by the published AuthenticatedSessionController, this base package class
 * provides the additional authentication checks above and beyond what Laravel Breeze is providing
 * (things link active and default roles checked and is user login currently active).
 */
class ehAuthenticatedSessionController extends Controller
{

    /**
     * Login message $keys are used with the corresponding ecoHelpers custom login validation checks.
     * The purpose is to allow for a more verbose error message (for testing and Demo) and then deploy the more obfuscated error numbers.
     *
     * @var string[]
     */
    protected $login_messages_array = [
        0=>['number'=>'Error: 400', 'description'=>'User model is not extended properly. Contact your system administrator.'],
        1=>['number'=>'Error: 495', 'description'=>'User profile is locked out. Contact your system administrator.'],
        2=>['number'=>'Error: 496', 'description'=>'User has no Acting Role. Contact your system administrator.'],
        3=>['number'=>'Error: 497', 'description'=>"User's Acting Role is not active. Contact your system administrator."],
        4=>['number'=>'Error: 498', 'description'=>"User has no default Group assigned. Contact your system administrator."],
        5=>['number'=>'Error: 499', 'description'=>"User's Group is not active. Contact your system administrator."],
    ];

    /**
     * This can be one of the 2 string key names above; 'number' or 'description'
     * and controls how login error messages are displayed as full verbose descriptions or just ambiguous numbers for more security.
     * @return mixed
     */
    protected $login_error_key = 'description';     // Less secure; tell exactly why you can't login.
    //TODO: Add this choice to eco-helpers.php
    //protected $login_error_key = 'number';        // More secure; just show an ambiguous error number (listed above).

    /**
     * Login username to be used by the controller. (email or name)
     *
     * @var string
     */

    // ?? This may have no affect when using Breeze. ??
    //protected $username;


    /**
     * Create a new controller instance with the appropriate middleware for the authentication system.
     *  (originally from the ehLoginController [ui] version)
     *  This looks to be specific to ui only. It breaks the Breeze call to destroy().
     *
     * @return void
     */

    public function __construct()
    {
        // $this->middleware('guest')->except('logout');
        // $this->middleware('web');


        // ?? This may have no affect when using Breeze. ??
        // Allow signing in with either username or email address.
        //$this->username = $this->findUsername();
    }


    protected function ehAdditionalLoginChecks($request) {

        ////////////////////////////////////////////////////////////////////////////////////////////
        // ecoHelpers additional login checks
        //TODO: Most of these same checks will need to be run when a user changes roles.
        // Should these all be in a callable method() / or trait?
        // Try to separate out the things that would be needed elsewhere and then see
        // what $variable (dependencies) issues may be created.
        // What would make sense for a trait name? UserSecurity@checkUser(id or Auth()->user) ??
        // NOT SURE THIS IS DOABLE -- or at least fairly challenging since the error messages for the login attemp
        // are all built in here. How would those be implemented in a trait??
        // And what use are they to a role change?? Could it throw you back to the login page with that message if appropriate ??


        ////////////////////////////////////////////////////////////////////////////////////////////

        // 1. Since we have to do this before the login attempt, let's see if we can even find the by email or username.
        //    If not, then just continue on and let the normal validation handle that.
        if(filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            // user is attempting login with email address
            $user = User::where('email',$request->email)->first();
        }
        else {
            // user is not using email address to login (check the username)
            $user = User::where('name',$request->email)->first();
        }

        // Was the user found? If not then we don't need to check any of this.
        // Just skip it and let the default login mechanism handle it.

        if (!empty($user->email)) {

            // Perform all the additional ecoHelpers login checks:
            $display_error_message = '';

            // Note: if the original user model is not extended from ehUser;
            //       you will get a message about isUserActive() missing.
            if (!method_exists($user,'isUserActive')) {
                $display_error_message = $this->login_messages_array[0][$this->login_error_key];
                $this->throwEcoHelperValidation($display_error_message);
            }

            // Note: see the class variables at the top that define the login message behavior using the $login_error_key.
            // 1. User's profile is not active.
            if (!$user->isUserActive($user->id)) {
                //if (!$user->login_active) {
                $display_error_message = $this->login_messages_array[1][$this->login_error_key];
                $this->throwEcoHelperValidation($display_error_message);
            }

            ///////////////////////////////////////////////////////////////////////////////////////////
            // 1.a - Before we do anything else, check the eh_role_lu table and see if we have any roles assigned (at all).
            $assigned_roles = ehRoleLookup::where('user_id',$user->id)->get();
            if (count($assigned_roles) < 1) {
                // Use the "No default group" error message.
                $display_error_message = $this->login_messages_array[4][$this->login_error_key];
            }

            // 2. User has no acting_role assigned.
            if (empty($user->getActingRole($user->id))) {    // Use the user function utility since it checks a couple things and assigns to default if missing.
                $display_error_message = $this->login_messages_array[2][$this->login_error_key];
            } else {
                // 3. User's acting_role is not active.
                if (!$user->isActingRoleActive($user->id)) {
                    $display_error_message = $this->login_messages_array[3][$this->login_error_key];
                }
            }

            // 4. User has no default_role assigned.
            // Note: Checking these sequentially like this enforces the behavior that:
            //       You must have an active default_role (even if your acting_role is active).
            if (empty($user->default_role)) {
                $display_error_message = $this->login_messages_array[4][$this->login_error_key];
            } else {
                // 5. User's default_role is not active.
                if (!ehRole::find($user->default_role)->active) {
                    $display_error_message = $this->login_messages_array[5][$this->login_error_key];
                }
            }


            // If we got here without already throwing an exception but we've previously set the error message.
            // THen go ahead and throw that message here.
            if ($display_error_message != '') {
                $this->throwEcoHelperValidation($display_error_message);
            }

            ////////////////////////////////////////////////////////////////////////////////////////////
            // This could be where we implement the "force password reset" code?
            // But for now, we're not including this feature in favor of the standard forgot password mechanism.
            // This was originally used by administrators setting up new users to force them
            // to set it the first login (again, already handled by the standard Laravel authentication)
            // Administrators can sill de-activate anyone's login as needed and force them to contact you for help.


            ////////////////////////////////////////////////////////////////////////////////////////////
            // Note: This is the end of the "preliminary" login checks.
            //       The user is not completely logged in yet so anything that should happen right after login
            //       must be handled below toward the end of the store() method.

        } else {

            // umm...we were unable to find this user.
            // For now we're doing nothing here -- we're assuming that the base Breeze quthentication catches that.

        }


    }



    /**
     * Get the login username to be used by the controller.
     *  (originally from the ehLoginController [ui] version)
     *
     *              // ?? This may have no affect when using Breeze. ??
     *
     * @return string
     */
    /*
    public function findUsername()
    {
        // Get the 'name to login with' field.
        $login = request()->input('email');   // The value from 'user name' on the authentication login form

        // If the $login value looks like and email address then use the 'email' field.
        // Otherwise use the 'name' field (which is the system generated user name.
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        request()->merge([$fieldType => $login]);

        return $fieldType;
    }
    */

    /**
     * Get username property. (override method)
     * Returns the field in the Users table that we want to search by.
     *  (originally from the ehLoginController [ui] version)
     *
     * @return string
     */
    public function username()
    {
        // ?? This may have no affect when using Breeze. ??
        return $this->username;
    }


    /**
     * Just a quick way to display various login message issues from
     *  within the multiple places they are checked above.
     *  (originally from the ehLoginController [ui] version)
     *
     * @param $display_error_message
     * @return void
     */
    protected function throwEcoHelperValidation($display_error_message) {
        // Throw the ecoHelpers additional login validation check message.
        // and display it under the username/email field in the login form.
        if (!empty($display_error_message)) {
            throw ValidationException::withMessages([
                // WARNING: if there's any kind of error in this line, the login goes through fine and skips the ValidationException below!!
                'email' => $display_error_message,
            ]);
        }
    }






    ///////////////////////////////////////////////////////////////////////////////////////////
    /**
     * These are from the Breeze AuthenticatedSessionController
     */
    /**
     * Display the login view.
     * (originally from the ehAuthenticatedSessionController [Breeze] version)
     */
    //public function create(): View
    public function create()
    {
        //TODO: Do we want to pursue the idea of being able to login to the uncontrolled Example Detail page
        // and then being able to stay on that page and just get the additional buttons to match your permissions?
        // For some reason this is different than intended() and returns back to the /eco HOME const.
        // dd(url()->previous());   // Maybe we could see if we were currently on any named route
        // other than '/' or '/eco' (which, by the way may go away later) -- and then act accordingly??

        return view('auth.login');
    }


    /**
     * Handle an incoming authentication request.
     * (originally from the ehAuthenticatedSessionController [Breeze] version)
     */
    //public function store(LoginRequest $request): RedirectResponse
    public function store(LoginRequest $request)
    {

        // hmm...should the additional eco-helpers checks be in front of or behind the normal authentication?
        // Leaving it in front for now. (So, errors, like role inactive or whatever display first).
        ////////////////////////////////////////////////////////////////////////////////////////////
        $this->ehAdditionalLoginChecks($request);
        ////////////////////////////////////////////////////////////////////////////////////////////

        $request->authenticate();
        $request->session()->regenerate();


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Add additional items after successful login:
        // This was the old "authenticated()" method from Laravel/UI.
        ////////////////////////////////////////////////////////////////////////////////////////////


        // 1.Update the user's last_login timestamp. (use the eco-helpers configured sql long time format)
        // date(ehConfig::get('date_sql_long')); // Appears to be identical to Carbon now()
        Auth()->user()->last_login = Carbon::now()->format(ehConfig::get('date_sql_long'));


        // 2.Increment the user's login counter.
        Auth()->user()->login_count = Auth()->user()->login_count + 1;


        // 3.Set the acting role at login to the default_role.
        Auth()->user()->setActingRole(Auth()->user()->roleAtLogin(),false);


        // 4.Save the changes set above to the user's record.
        Auth()->user()->save();


        // 6.And finally, redirect to where the login person should go.
        if (!empty(Auth()->user()->getDefaultHomePage())) {

            // If the user's acting role has a default_home_page defined then use it.
            return redirect()->intended(route(Auth()->user()->getDefaultHomePage()->route));

        } else {

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Go to the intended route or the global HOME page.
            /*
            if (empty(ehConfig::get('access.login_home_page'))) {
                // If the key in eco-helpers in cleared out then use whatever is defined in this constant.
                return redirect()->intended(RouteServiceProvider::HOME);
            } elseif (!empty(RouteServiceProvider::HOME)) {
                return redirect()->intended(RouteServiceProvider::HOME);
            } else {
                // If the key has something in it, then use it.
                return redirect()->intended(ehConfig::get('access.login_home_page'));
            }
            */

            if (!empty(ehConfig::get('access.login_home_page'))) {
                return redirect()->intended(ehConfig::get('access.login_home_page'));
            } elseif (!empty(RouteServiceProvider::HOME)) {
                return redirect()->intended(RouteServiceProvider::HOME);
            } else {
                return redirect()->intended();
            }


            // TESTING TRYING TO CONTINUE ON TO AN INTENDED ROUTE.
            //return redirect()->intended(RouteServiceProvider::HOME);    // This works if you hit a protected route and it forces you to login.
            // But if you're on a page and just want to get edit rights by logging in --
            // then it redirects to HOME.
            // Or does it make more sense to return to the page you're already on?
            // dd(request()->route()->getName());    // This is null (?)
            // return redirect()->back();            // This goes back to the /eco home page (when on examples@show())
            // return redirect()->intended();        // This goes to the Laravel '/' home page
        }

    }


    /**
     * Destroy an authenticated session.
     * (originally from the ehAuthenticatedSessionController [Breeze] version)
     */

    //public function destroy(Request $request): RedirectResponse
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();


        ////////////////////////////////////////////////////////////////////////////////////////////
        // Go here after logging out.
        if (empty(ehConfig::get('access.logout_home_page'))) {
            // If the key in eco-helpers in cleared out then use this for the default.
            return redirect('/');
        } else {
            // If the key has something in it, then use it.
            return redirect(ehConfig::get('access.logout_home_page'));
        }

    }

}
