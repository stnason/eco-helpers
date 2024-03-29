<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Web;
use ScottNason\EcoHelpers\Models\ehUser;
use ScottNason\EcoHelpers\Classes\ehNotifier;
use ScottNason\EcoHelpers\Controllers\ehPagesController;
use ScottNason\EcoHelpers\Controllers\ehRolesController;
use ScottNason\EcoHelpers\Controllers\ehUsersController;
use ScottNason\EcoHelpers\Controllers\ehConfigController;
use ScottNason\EcoHelpers\Controllers\ehImportExportController;

///////////////////////////////////////////////////////////////////////////////////////////
// THESE SHOULDN'T NEED TO BE IN A GROUP. THEY ARE ALREADY CONTROLLED IN ehBaseController
//  based on the eh_pages setting.
// Note: Any controller that properly extends ehBaseController should already be doing this.
//Route::middleware('auth', 'verified')->group(function () {
    ///////////////////////////////////////////////////////////////////////////////////////////
    // Resourceful routes
    Route::resource('pages', ehPagesController::class);
    Route::resource('roles', ehRolesController::class);
    Route::resource('users', ehUsersController::class);
    Route::resource('config', ehConfigController::class);

    ///////////////////////////////////////////////////////////////////////////////////////////
    // - Role maintenance.
    Route::delete('/delete-role-from-user/{role_lookup}', [ehUser::class, 'deleteRoleFromUser']);
    Route::delete('/delete-user-from-role', [ehUser::class, 'removeUsersFromRole']);
    Route::post('/users/role', [ehUsersController::class, 'role'])->name('users.role');

    ///////////////////////////////////////////////////////////////////////////////////////////
    // Generic Import Export controllers
    Route::get('/export/{table_name?}', [ehImportExportController::class, 'export'])->name('export');
    //Route::get('/export_restricted', 'ImportExportController@export_restricted')->name('export_restricted');
    //Route::get('/import/{table_name}', 'ImportExportController@import')->name('import');
    //Route::get('/progress/read/{file_name}', 'ProgressController@readProgress')->name('progress.read');
//});


///////////////////////////////////////////////////////////////////////////////////////////
// - Notifications API; Note these all need the 'web' middleware to have access to the session!
Route::middleware('web')->group(function () {
    Route::post('/notifications/get-next',[ehNotifier::class, 'getNext'])->name('notifications.get-next');
    //Route::get('/notifications/get-next',[ehNotifier::class, 'getNext'])->name('notifications.get-next');
    Route::get('/notifications/get-next',[ehNotifier::class, 'getNext']);
    Route::post('/notifications/delete-next',[ehNotifier::class,'deleteNext'])->name('notifications.delete-next');
    Route::post('/notifications/get-total',[ehNotifier::class,'getTotal'])->name('notifications.get-total');
});
