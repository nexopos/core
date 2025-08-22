<?php

namespace Ns\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Ns\Exceptions\NotAllowedException;
use Ns\Models\Role;
use Ns\Services\Helper;

class NotInstalledStateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
        /**
         * we'll try to detect the language
         * from the query string.
         */
        if ( ! empty( $request->query( 'lang' ) ) ) {
            $validLanguage = in_array( $request->query( 'lang' ), array_keys( config( 'nexopos.languages' ) ) ) ? $request->query( 'lang' ) : 'en';
            App::setLocale( $validLanguage );
        }

        /**
         * We'll also check if there is at least
         * and administrator. If he's deleted, it will be required to create a new one.
         */
        $totalAdmins = Role::withNamespace( Role::ADMIN )->whereHas( 'users' )->count();

        if ( ! Helper::installed() || $totalAdmins === 0 ) {
            return $next( $request );
        }

        throw new NotAllowedException( __( 'You\'re not allowed to see this page.' ) );
    }
}
