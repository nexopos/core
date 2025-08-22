<?php

/**
 * @var WidgetService $widgetService
 */

use Ns\Models\Permission;
use Ns\Services\WidgetService;

$widgetService = app()->make( WidgetService::class );

$widgets = $widgetService->getAllWidgets();

if ( defined( 'NEXO_CREATE_PERMISSIONS' ) ) {
    $widgets->each( function ( $widget ) {
        /**
         * The permission is created only
         * if the widget declares some permission.
         */
        if ( is_string( $widget->instance->getPermission() ) ) {
            $taxes = Permission::firstOrNew( [ 'namespace' => $widget->instance->getPermission() ] );
            $taxes->name = sprintf( __( 'Widget: %s' ), $widget->instance->getName() );
            $taxes->namespace = $widget->instance->getPermission();
            $taxes->description = $widget->instance->getDescription();
            $taxes->save();
        }
    } );
}
