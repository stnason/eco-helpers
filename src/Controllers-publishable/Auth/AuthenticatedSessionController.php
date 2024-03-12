<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ScottNason\EcoHelpers\Controllers\Auth\ehAuthenticatedSessionController;

/**
 * This is the package published version that extends the ehAuthenticatedSessionController
 * which is providing the additional package permission checks.
 *
 */
class AuthenticatedSessionController extends ehAuthenticatedSessionController
{



}
