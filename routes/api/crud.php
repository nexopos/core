<?php

use Ns\Classes\Hook;
use Ns\Http\Controllers\Dashboard\CrudController;
use Illuminate\Support\Facades\Route;

Route::get( 'crud/{namespace}', [ CrudController::class, 'crudList' ] );
Route::get( 'crud/{namespace}/columns', [ CrudController::class, 'getColumns' ] );
Route::get( 'crud/{namespace}/config', [ CrudController::class, 'getConfig' ] );
// Route::get( 'crud/{namespace}/form-config/{id?}', [ CrudController::class, 'getFormConfig' ] );
Route::get( 'crud/{namespace}/form/{id?}', [ CrudController::class, 'getFormStructure' ] );
Route::put( 'crud/{namespace}/{id}', [ CrudController::class, 'crudPut' ] );
Route::post( 'crud/{namespace}', [ CrudController::class, 'crudPost' ] );
Route::post( 'crud/{namespace}/export', [ CrudController::class, 'exportCrud' ] );
Route::post( 'crud/{namespace}/bulk-actions', [ CrudController::class, 'crudBulkActions' ] )->name( Hook::filter( 'ns-route-name', 'ns.api.crud-bulk-actions' ) );
Route::delete( 'crud/{namespace}/{id}', [ CrudController::class, 'crudDelete' ] );
