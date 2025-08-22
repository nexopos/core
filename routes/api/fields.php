<?php

use Illuminate\Support\Facades\Route;
use Ns\Http\Controllers\Dashboard\FieldsController;

Route::get( '/fields/{resource}/{identifier?}', [ FieldsController::class, 'getFields' ] );
Route::post( '/fields/{resource}/{identifier?}', [ FieldsController::class, 'updateFields' ] );
