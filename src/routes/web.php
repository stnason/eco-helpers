<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Web;
use ScottNason\EcoHelpers\Classes\ehNotifier;
use ScottNason\EcoHelpers\Controllers\ehPagesController;
use ScottNason\EcoHelpers\Controllers\ehRolesController;
use ScottNason\EcoHelpers\Controllers\ehUsersController;
use ScottNason\EcoHelpers\Controllers\ehConfigController;
use ScottNason\EcoHelpers\Models\ehUser;

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
// - Notifications API; Note these all need the 'web' middleware to have access to the session!
Route::post('/notifications/get-next',[ehNotifier::class, 'getNext'])->name('notifications.get-next')->middleware('web');
//Route::get('/notifications/get-next',[ehNotifier::class, 'getNext'])->name('notifications.get-next')->middleware('web');;
Route::get('/notifications/get-next',[ehNotifier::class, 'getNext'])->middleware('web');;
Route::post('/notifications/delete-next',[ehNotifier::class,'deleteNext'])->name('notifications.delete-next')->middleware('web');
Route::post('/notifications/get-total',[ehNotifier::class,'getTotal'])->name('notifications.get-total')->middleware('web');