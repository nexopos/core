<?php

use Illuminate\Support\Facades\Route;
use Ns\Http\Controllers\Dashboard\ResetController;

Route::post( 'reset', [ ResetController::class, 'truncateWithDemo' ] )->name( 'ns.reset' );
