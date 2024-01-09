<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use ScottNason\EcoHelpers\Classes\ehConfig;

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
            'login_created'=>date("Y-m-d"),             // Stamp the time this login was created.
            'name' => $this->uniqueUserName($request),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'login_active' => 1,                                // All new registrations will start out as active.
            'force_password_reset' => 0,                        // An admin function to force a user to change their password.
            'default_role' => ehConfig::get('new_user_role'),   // The default user role specified in the config file.
            'acting_role' =>  ehConfig::get('new_user_role'),   // Set acting role to same as the initial default role.
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('eco', absolute: false));
    }




    /**
     * Create a unique user name base on this specific algorithm.
     * @param Request $request
     * @return string
     */
    protected function uniqueUserName(Request $request) {
        $user_name = '';
        $user_name =    substr(strtolower($request->first_name),0,3) . substr(strtolower($request->last_name),0,3);


        // Determine if this user name is unique. (And create a unique one if needed by adding a number)
        $name_is_unique = false;            // Check to see if this user name is unique among all users.
        $unique_cnt = 1;                    // A number to add after the user name to make it unique.
        $unique_user_name = $user_name;     // The newly created unique user name.
        do {
            $r = DB::select("SELECT * FROM users WHERE name = '".$unique_user_name."';");
            if ($r->count() > 0) {          // This name is already in use.
                $unique_user_name = $user_name.$unique_cnt;
                $unique_cnt++;
            } else {
                $name_is_unique = true;     // Drop us out of this unique check and return this version of the user name.
            }
        }  while (!$name_is_unique);

        return $unique_user_name;
    }


}
