<?php

use Illuminate\Support\Facades\Route;
use Ns\Http\Controllers\Dashboard\FormsController;

Route::get( '/forms/{resource}/{identifier?}', [ FormsController::class, 'getForm' ] )->name( 'ns.forms.get' );
Route::post( '/forms/{resource}/{identifier?}', [ FormsController::class, 'saveForm' ] )->name( 'ns.forms.post' );
