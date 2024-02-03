<?php

use Illuminate\Support\Facades\Route;
use ScottNason\EcoHelpers\Controllers\ehNotificationsController;
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
// - Notifications API
Route::post('/notifications/get-next',[ehNotificationsController::class, 'getNext'])->name('notifications.get-next');
//Route::get('/notifications/get-next',[ehNotificationsController::class, 'getNext'])->name('notifications.get-next');
Route::get('/notifications/get-next',[ehNotificationsController::class, 'getNext']);
Route::post('/notifications/delete-next',[ehNotificationsController::class,'deleteNext'])->name('notifications.delete-next');
Route::post('/notifications/get-total',[ehNotificationsController::class,'getTotal'])->name('notifications.get-total');