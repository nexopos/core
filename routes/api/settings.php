<?php

use Illuminate\Support\Facades\Route;
use Ns\Http\Controllers\Dashboard\SettingsController;

Route::get( '/settings/{identifier}', [ SettingsController::class, 'getSettingsForm' ] );
Route::post( '/settings/{identifier}', [ SettingsController::class, 'saveSettingsForm' ] );
