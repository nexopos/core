<?php

use Illuminate\Support\Facades\Route;
use Ns\Http\Controllers\DashboardController;
use Ns\Http\Middleware\NsRestrictMiddleware;

Route::get( '', [ DashboardController::class, 'home' ] )->name( nsRouteName( 'ns.dashboard.home' ) )
    ->middleware( [ NsRestrictMiddleware::arguments( 'read.dashboard' )] );

include dirname( __FILE__ ) . '/web/medias.php';
include dirname( __FILE__ ) . '/web/settings.php';
