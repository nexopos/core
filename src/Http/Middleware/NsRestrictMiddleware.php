<?php

namespace Ns\Http\Middleware;

use Ns\Exceptions\NotEnoughPermissionException;
use Ns\Traits\NsMiddlewareArgument;
use Closure;
use Illuminate\Http\Request;

class NsRestrictMiddleware
{
    use NsMiddlewareArgument;

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle( Request $request, Closure $next, $permission )
    {
        // if the permission has ":any:" we will expect the permission to be separated by "|"
        // and we'll check if the user has at least one of the permissions.

        if ( str_contains( $permission, 'any:' ) ) {
            $permissions = explode( '|', str_replace( 'any:', '', $permission ) );

            foreach ( $permissions as $perm ) {
                if ( ns()->allowedTo( $perm ) ) {
                    return $next( $request );
                }
            }

            $message = sprintf(
                __( 'You don\'t have enough permission for any of the following: "%s".' ),
                implode( ', ', $permissions )
            );

            throw new NotEnoughPermissionException( $message );

        } elseif ( ns()->allowedTo( $permission ) ) {
            return $next( $request );
        }

        $message = sprintf(
            __( 'Your don\'t have enough permission ("%s") to perform this action.' ),
            $permission
        );

        throw new NotEnoughPermissionException( $message );
    }
}
