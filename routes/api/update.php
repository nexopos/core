<?php

use Illuminate\Support\Facades\Route;
use Ns\Http\Controllers\UpdateController;
use Ns\Http\Middleware\Authenticate;
use Ns\Http\Middleware\CheckMigrationStatus;

Route::post( 'update', [ UpdateController::class, 'runMigration' ] )
    ->withoutMiddleware( [ Authenticate::class, CheckMigrationStatus::class ] );
