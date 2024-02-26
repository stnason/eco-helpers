<?php

/**
 * This trait is responsible for checking the user basic login permissions
 * on initial login and when changing roles.
 *
 * Usage: Include "use App/Traits/ehUserSecurity" in the Class declaration
 *
 */

namespace ScottNason\EcoHelpers\Traits;


use App\Models\User;

trait ehUserSecurity
{
    /**
     * This check can (should) be done before the user is logged in, so it require a unique login identifier: email.
     *
     * @param $email    // The user's login registered email address.
     * @return boolean  // Did we pass this set of checks or not?
     */
    public static function checkUser($email) {

        // 1. Since we can do this before the login attempt, let's see if we can even find the user.
        $user = User::where('email',$request->email)->first();

        // 2. If we could not find the user, then just return with a fail.
        if (empty($user->email)) { return false; }



    }
}
