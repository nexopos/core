<?php

namespace Ns\Http\Controllers\Dashboard;

use Exception;
use Illuminate\Http\Request;
use Ns\Http\Controllers\DashboardController;
use Ns\Services\FieldsService;
use TorMorten\Eventy\Facades\Events as Hook;

class FieldsController extends DashboardController
{
    public function getFields( $resource, $identifier = null )
    {
        $instance = Hook::filter( 'ns.fields', $resource, $identifier );

        if ( ! $instance instanceof FieldsService ) {
            throw new Exception( sprintf( __( '"%s" is not an instance of "FieldsService"' ), $resource ) );
        }

        return $instance->get( $identifier );
    }

    public function updateFields( Request $request, $resource, $identifier = null )
    {
        $instance = Hook::filter( 'ns.fields', $resource, $identifier );

        if ( ! $instance instanceof FieldsService ) {
            throw new Exception( sprintf( __( '"%s" is not an instance of "FieldsService"' ), $resource ) );
        }

        if ( ! method_exists( $instance, 'handlePost' ) ) {
            throw new Exception( sprintf( __( '"%s" does not implement "handlePost" method' ), $resource ) );
        }

        return $instance->handlePost( $request->all(), $identifier );
    }
}
