<?php

use Illuminate\Support\Facades\Route;
use Ns\Http\Controllers\Dashboard\SettingsController;

Route::get( '/settings/{settings}', [ SettingsController::class, 'getSettings' ] )->name( nsRouteName( 'ns.dashboard.settings' ) );
