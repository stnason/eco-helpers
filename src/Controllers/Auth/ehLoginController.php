<?php

/**
 * DEPRECATED - we're going to use Breeze as the starting point instead of UI
 * This is for use with the publishable LoginController for use with the Laravel/UI scaffolding.
 */

namespace ScottNason\EcoHelpers\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


use ScottNason\EcoHelpers\Models\ehRole;
use App\Models\User;

// getting an error that the trait is missing:
// [2023-09-10 11:17:19] local.ERROR: Trait "ScottNason\EcoHelpers\Controllers\Auth\ScottNason\EcoHelpers\Traits\ehProperExtendCheck" not found {"exception":"[object] (Symfony\\Component\\ErrorHandler\\Error\\FatalError(code: 0): Trait \"ScottNason\\EcoHelpers\\Controllers\\Auth\\ScottNason\\EcoHelpers\\Traits\\ehProperExtendCheck\" not found at /home/dh_s4mvcz/_sites_private/_private_nasonstudios.com/vendor/scott-nason/eco-helpers/src/Controllers/Auth/ehLoginController.php:17)
// For some reason, had to add the leading backslash to correct a "missing trait" error.
//use \ScottNason\EcoHelpers\Traits\ehProperExtendCheck;
use ScottNason\EcoHelpers\Traits\ehLoginAndAuthenticatedSessionsFunctions;
use ScottNason\EcoHelpers\Traits\ehProperExtendCheck;


class ehLoginController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use AuthenticatesUsers;
    use ehProperExtendCheck;
    // These are the specific eco-helpers add-ons into the auth system.
    // Note: they are shared between Laravel/ui and Laravel/Breeze.
    use ehLoginAndAuthenticatedSessionsFunctions;







    /*  -- Just leaving this here for a little while as a reminder of what I've been through to get here.
           Most of the layout init stuff is now being done in the blade _password-framework template.
    public function showLoginForm(Request $request)
    {
        // 11/30/2023 -- the get /login route comes here.


        //dd('attempting to show a login screen');
        ehLayout::initLayout();
        ehLayout::setAll(false);    // turn off all of the page area displays.


        $form['layout'] = ehLayout::getLayout();
        $form['layout']['card_header'] = 'Sign In';
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the form action
        $form['layout']['form_action'] = route('login');
        $form['layout']['form_method'] = 'POST';
        return view('ecoHelpers::auth.login',['form'=>$form]);    // no css; no base template
        //return null;

        // !!!
        // Not sure -- testing this out to see if we can get rid of the missing Layout error message
        // when calling the get version of /login.
        // But kind of chasing my tail here with ehBaseController@forceLogin() ??
        // !!!
        //
        //
        // ehBaseController::forceLogin();
        //ehLayout::initLayout();
        //$form['layout'] = ehLayout::getLayout();
        //return view('auth.login', ['form' => $form]);
    }
*/









}