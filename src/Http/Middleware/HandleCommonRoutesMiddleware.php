<?php

namespace Ns\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ns\Classes\Hook;

class HandleCommonRoutesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle( Request $request, Closure $next )
    {
        $resultRequest = Hook::filter( 'ns-common-routes', false, $request, $next );

        if ( $resultRequest === false ) {
            return $next( $request );
        }

        return $resultRequest;
    }
}
