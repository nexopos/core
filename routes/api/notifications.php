<?php

use Illuminate\Support\Facades\Route;
use Ns\Http\Controllers\Dashboard\NotificationsController;

Route::get( 'notifications', [ NotificationsController::class, 'getNotifications' ] );
Route::delete( 'notifications/{id}', [ NotificationsController::class, 'deleteSingleNotification' ] )->where( [ 'id' => '[0-9]+' ] );
Route::delete( 'notifications/all', [ NotificationsController::class, 'deletAllNotifications' ] );
