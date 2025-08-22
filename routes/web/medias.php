<?php

use Illuminate\Support\Facades\Route;
use Ns\Http\Controllers\Dashboard\MediasController;

Route::get( '/medias', [ MediasController::class, 'showMedia' ] )->name( nsRouteName( 'ns.dashboard.medias' ) );
