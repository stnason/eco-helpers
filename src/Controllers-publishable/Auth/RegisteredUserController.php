<?php

namespace App\Http\Controllers\Auth;

use ScottNason\EcoHelpers\Rules\CheckEmails;

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
use ScottNason\EcoHelpers\Classes\ehConfig;

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
            //'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            //'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],    // Lowercase? Really?
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],

            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            //'name' => $request->name,     // Unless I modify it, the original Laravel migration doesn't include a default value so it needs something.
            //'login_created'=>date("Y-m-d"),             // Stamp the time this login was created.
            'login_created'=>date(ehConfig::get('date_format_sql_long')),             // Stamp the time this login was created.
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

        return redirect('/');
    }




}
