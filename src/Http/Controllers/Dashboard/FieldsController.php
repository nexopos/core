<?php

namespace Ns\Http\Controllers\Dashboard;

use Ns\Http\Controllers\DashboardController;
use Ns\Services\FieldsService;
use Exception;
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
}
