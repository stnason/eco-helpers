<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ehHomeController;
use ScottNason\EcoHelpers\Controllers\ehExamplesController;


///////////////////////////////////////////////////////////////////////////////////////////
// These are test and sample routes to use on initial install of eco-helpers.
// They can be included in your web.php file for a while and then removed:
// require __DIR__.'/../vendor/scott-nason/eco-helpers/src/routes/eco-sample-routes.php';

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
Route::resource('examples', ehExamplesController::class);

///////////////////////////////////////////////////////////////////////////////////////////
// Named routes.
// - Initial OOTB splash screen and examples.
Route::get('/eh-home',[ehHomeController::class, 'index'])->name('eh-home');

