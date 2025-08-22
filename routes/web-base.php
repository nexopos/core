<?php

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Ns\Events\WebRoutesLoadedEvent;
use Ns\Http\Controllers\Dashboard\CrudController;
use Ns\Http\Controllers\Dashboard\HomeController;
use Ns\Http\Controllers\SetupController;
use Ns\Http\Controllers\UpdateController;
use Ns\Http\Middleware\CheckApplicationHealthMiddleware;
use Ns\Http\Middleware\CheckMigrationStatus;
use Ns\Http\Middleware\ClearRequestCacheMiddleware;
use Ns\Http\Middleware\HandleCommonRoutesMiddleware;
use Ns\Http\Middleware\InstalledStateMiddleware;
use Ns\Http\Middleware\NotInstalledStateMiddleware;

Route::middleware( [ 'web' ] )->group( function () {
    Route::get( '/', [ HomeController::class, 'welcome' ] )->name( 'ns.welcome' );
} );

require dirname( __FILE__ ) . '/intermediate.php';

Route::middleware( [
    InstalledStateMiddleware::class,
    CheckMigrationStatus::class,
    SubstituteBindings::class,
] )->group( function () {
    /**
     * We would like to isolate certain routes as it's registered
     * for authentication and are likely to be applicable to sub stores
     */
    require dirname( __FILE__ ) . '/authenticate.php';

    Route::get( '/database-update', [ UpdateController::class, 'updateDatabase' ] )
        ->withoutMiddleware( [ CheckMigrationStatus::class ] )
        ->name( 'ns.database-update' );

    Route::middleware( [
        'auth',
        CheckApplicationHealthMiddleware::class,
        ClearRequestCacheMiddleware::class,
    ] )->group( function () {
        Route::prefix( 'dashboard' )->group( function () {
            event( new WebRoutesLoadedEvent( 'dashboard' ) );

            Route::middleware( [
                HandleCommonRoutesMiddleware::class,
            ] )->group( function () {
                require dirname( __FILE__ ) . '/common.php';
            } );

            include dirname( __FILE__ ) . '/web/modules.php';
            include dirname( __FILE__ ) . '/web/users.php';

            Route::get( '/crud/download/{hash}', [ CrudController::class, 'downloadSavedFile' ] )->name( 'ns.dashboard.crud-download' );
        } );
    } );
} );

Route::middleware( [
    NotInstalledStateMiddleware::class,
    ClearRequestCacheMiddleware::class,
] )->group( function () {
    Route::prefix( '/do-setup/' )->group( function () {
        Route::get( '', [ SetupController::class, 'welcome' ] )->name( 'ns.do-setup' );
    } );
} );
