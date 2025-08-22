<?php

namespace Ns\Exceptions;

use Exception;
use Ns\Services\Helper;

class NotFoundAssetsException extends Exception
{
    public function __construct( $message = null )
    {
        $this->message = $message ?: __( 'Unable to locate the assets.' );
    }

    public function render( $request )
    {
        $message = $this->getMessage();
        $title = __( 'Not Found Assets' );
        $back = Helper::getValidPreviousUrl( $request );

        return response()->view( 'pages.errors.assets-exception', compact( 'message', 'title', 'back' ), 500 );
    }
}
