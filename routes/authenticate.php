<?php

use Illuminate\Support\Facades\Route;
use Ns\Http\Controllers\AuthController;
use Ns\Http\Middleware\PasswordRecoveryMiddleware;
use Ns\Http\Middleware\RegistrationMiddleware;

Route::get( '/sign-in', [ AuthController::class, 'signIn' ] )->name( nsRouteName( 'ns.login' ) );
Route::get( '/auth/activate/{user}/{token}', [ AuthController::class, 'activateAccount' ] )->name( nsRouteName( 'ns.activate-account' ) );
Route::get( '/new-password/{user}/{token}', [ AuthController::class, 'newPassword' ] )->name( nsRouteName( 'ns.new-password' ) );
Route::get( '/sign-out', [ AuthController::class, 'signOut' ] )->name( nsRouteName( 'ns.logout' ) );
Route::post( '/auth/sign-in', [ AuthController::class, 'postSignIn' ] )->name( nsRouteName( 'ns.login.post' ) );

/**
 * should protect access with
 * the registration is explictely disabled
 */
Route::middleware( [
    RegistrationMiddleware::class,
] )->group( function () {
    Route::post( '/auth/sign-up', [ AuthController::class, 'postSignUp' ] )->name( nsRouteName( 'ns.register.post' ) );
    Route::get( '/sign-up', [ AuthController::class, 'signUp' ] )->name( nsRouteName( 'ns.register' ) );
} );

/**
 * Should protect recovery when the
 * recovery is explicitly disabled
 */
Route::middleware( [
    PasswordRecoveryMiddleware::class,
] )->group( function () {
    Route::get( '/password-lost', [ AuthController::class, 'passwordLost' ] )->name( nsRouteName( 'ns.password-lost' ) );
    Route::post( '/auth/password-lost', [ AuthController::class, 'postPasswordLost' ] )->name( nsRouteName( 'ns.password-lost.post' ) );
    Route::post( '/auth/new-password/{user}/{token}', [ AuthController::class, 'postNewPassword' ] )->name( nsRouteName( 'ns.post.new-password' ) );
} );
