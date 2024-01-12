<?php

use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\ehHomeController;
use ScottNason\EcoHelpers\Controllers\ehNotificationsController;
use ScottNason\EcoHelpers\Controllers\ehPagesController;
use ScottNason\EcoHelpers\Controllers\ehRolesController;
use ScottNason\EcoHelpers\Controllers\ehUsersController;
use ScottNason\EcoHelpers\Controllers\ehConfigController;
use ScottNason\EcoHelpers\Controllers\ehExamplesController;
use ScottNason\EcoHelpers\Models\ehUser;

///////////////////////////////////////////////////////////////////////////////////////////
// Set up a couple aliases to the published ehHomeController
// TODO: Will need to decide exactly what to leave here for the final version.
//        I don't think it's going to be this, though. Either something way simpler
//        or nothing at all. (?) What do we want to happen on initial install?

Route::get('/{url}', function ($url) {
    return Redirect::to('/eh-home');
})->where(['url' => 'eco|ecohelpers|eco-helpers']);
Route::get('/eco', [ehHomeController::class, 'index'])->name('eco');

/* If we use Breeze scaffolding, looks like this is already taken care of.
Route::group(['middleware' => ['web']], function () {
    // Applying the web middleware here is needed,
    // otherwise this logout route doesn't have access to the session for some reason.
    Route::get('/logout', [LoginController::class, 'logout']);
});
*/


///////////////////////////////////////////////////////////////////////////////////////////
// Named routes that share a resource route name must be defined before the resource route.
Route::get('/examples/static-page', [ehExamplesController::class, 'staticPage'])->name('examples.static-page');

///////////////////////////////////////////////////////////////////////////////////////////
// Resourceful routes
Route::resource('pages', ehPagesController::class);
Route::resource('roles', ehRolesController::class);
Route::resource('users', ehUsersController::class);
Route::resource('config', ehConfigController::class);
Route::resource('examples', ehExamplesController::class);


///////////////////////////////////////////////////////////////////////////////////////////
// Named routes.

// - Initial OOTB splash screen and examples.
Route::get('/eh-home',[ehHomeController::class, 'index'])->name('eh-home');

// - Role maintenance.
Route::delete('/delete-role-from-user/{role_lookup}', [ehUser::class, 'deleteRoleFromUser']);
Route::delete('/delete-user-from-role', [ehUser::class, 'removeUsersFromRole']);
Route::post('/users/role', [ehUsersController::class, 'role'])->name('users.role');

// - Notifications API
Route::post('/notifications/get-next',[ehNotificationsController::class, 'getNext'])->name('notifications.get-next');
//Route::get('/notifications/get-next',[ehNotificationsController::class, 'getNext'])->name('notifications.get-next');
Route::get('/notifications/get-next',[ehNotificationsController::class, 'getNext']);
Route::post('/notifications/delete-next',[ehNotificationsController::class,'deleteNext'])->name('notifications.delete-next');
Route::post('/notifications/get-total',[ehNotificationsController::class,'getTotal'])->name('notifications.get-total');