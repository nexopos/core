<?php

namespace Ns\Exceptions;

use Exception;
use Ns\Classes\JsonResponse;
use Ns\Services\Helper;

class NotAllowedException extends Exception
{
    public function __construct( $message = null )
    {
        $this->message = $message ?: __( 'The Action You Tried To Perform Is Not Allowed.' );
    }

    public function getStatusCode()
    {
        return 403;
    }

    public function render( $request )
    {
        if ( ! $request->expectsJson() ) {
            return response()->view( 'ns::pages.errors.not-allowed', [
                'title' => __( 'Not Allowed Action' ),
                'message' => $this->getMessage(),
                'back' => Helper::getValidPreviousUrl( $request ),
            ], $this->getStatusCode() );
        }

        return JsonResponse::error(
            message: $this->getMessage() ?: __( 'The action you tried to perform is not allowed.' )
        );
    }
}
