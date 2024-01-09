<?php
/**
 * This will be used with the publishable AuthenticationSessionController for use with the Breeze scaffolding.
 */

namespace ScottNason\EcoHelpers\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ScottNason\EcoHelpers\Traits\ehLoginAndAuthenticatedSessionsFunctions;
use ScottNason\EcoHelpers\Traits\ehProperExtendCheck;

class ehAuthenticatedSessionController extends Controller
{

    use ehProperExtendCheck;

    // These are the specific eco-helpers add-ons into the auth system.
    use ehLoginAndAuthenticatedSessionsFunctions;




    //TODO: I think everything from the ehLoginAndAuthenticatedSessionsFunctions trait can be moved back in here now.
    // The original reason to break it out was so that UI and Breeze controllers could share it.
    // (It get confusing having this empty controller to extend for no real benefit.)


}
