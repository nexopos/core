<?php

namespace Ns\Http\Middleware;

use Ns\Events\ApiMiddlewareTriggeredEvent;

class ApiMiddleware
{
    public function handle( $request, \Closure $next )
    {
        ApiMiddlewareTriggeredEvent::dispatch( $request );

        return $next( $request );
    }
}
