<?php

namespace Ns\Exceptions;

use Exception;
use Ns\Services\Helper;

class MissingDependencyException extends Exception
{
    public function __construct( $message = null )
    {
        $this->message = $message ?: __( 'There is a missing dependency issue.' );
    }

    public function render( $request )
    {
        if ( ! $request->expectsJson() ) {
            return response()->view( 'pages.errors.missing-dependency', [
                'title' => __( 'Missing Dependency' ),
                'message' => $this->getMessage(),
                'back' => Helper::getValidPreviousUrl( $request ),
            ] );
        }

        return response()->json( [
            'status' => 'error',
            'message' => $this->getMessage(),
        ], 401 );
    }
}
