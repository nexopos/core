<?php

use Ns\Http\Controllers\Dashboard\FormsController;
use Illuminate\Support\Facades\Route;

Route::get( '/forms/{resource}/{identifier?}', [ FormsController::class, 'getForm' ] )->name( 'ns.forms.get' );
Route::post( '/forms/{resource}/{identifier?}', [ FormsController::class, 'saveForm' ] )->name( 'ns.forms.post' );