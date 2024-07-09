<?php

namespace App\Http\Controllers\Auth;

use ScottNason\EcoHelpers\Rules\CheckEmails;
use ScottNason\EcoHelpers\Classes\ehConfig;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;


/**
 * The package published version of the Laravel Breeze Controller.
 *
 */
class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {

        $request->validate([
            //TODO: I don't think this is a complete implementation of this. (where is the site_key checked?)
            // But it looks to be enough to thwart the auto register bots.
            'g-recaptcha-response' => 'required',   // Google ReCaptcha Validation
            //'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            //'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],    // Lowercase? Really?
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ],
            [
                'g-recaptcha-response.required' => 'You must complete the reCAPTCHA form.',
            ]
        );

        $user = User::create([
            //'name' => $request->name,     // Unless I modify it, the original Laravel migration doesn't include a default value so it needs something.
            //'login_created'=>date("Y-m-d"),             // Stamp the time this login was created.

            // Stamp the time this login was created.
            // date(ehConfig::get('date_format_sql_long')); // Appears to be identical to Carbon now()
            'login_created'=> Carbon::now()->format(ehConfig::get('date_format_sql_long')),
            'name' => User::uniqueUserName($request),
            'account_id' => User::uniqueAccountNumber($request),// Create a unique account number for this user.
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'login_active' => 1,                                // All new registrations will start out as active.
            'force_password_reset' => 0,                        // An admin function to force a user to change their password.
            'default_role' => ehConfig::get('new_user_role'),   // The default user role specified in the config file.
            'acting_role' =>  ehConfig::get('new_user_role'),   // Set acting role to same as the initial default role.
            'email' => $request->email,
            'email_personal' =>  $request->email,               // Save the registered email as the personal for now.
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);


        if (!empty(config('eco-helpers.login_home_page'))) {

            // Use the login page defined in the config file
            // Note: this is not a route, but a url!
            // prepend a forward slash if it's missing
            $redirect = config('eco-helpers.login_home_page');
            if (substr($redirect, 0, 1) != '/') {
                $redirect = '/'.config('eco-helpers.login_home_page');
            }
            return redirect($redirect);

        } else {
            return redirect('/');       // If the eco-helpers key is blank then use '/'
        }

    }




}
