<?php

namespace Ns\Http\Middleware;

use Ns\Events\LocaleDefinedEvent;
use Ns\Models\UserAttribute;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class LoadLangMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle( Request $request, Closure $next )
    {
        if ( Auth::check() ) {
            $userAttribute = Auth::user()->attribute;

            $language = ns()->option->get( 'ns_store_language', 'en' );

            /**
             * if the user attribute is not defined,
             * we'll use the default system locale or english by default.
             */
            if ( $userAttribute instanceof UserAttribute ) {
                $language = Auth::user()->attribute->language;
            }

            App::setLocale( in_array( $language, array_keys( config( 'nexopos.languages' ) ) ) ? $language : 'en' );
        } else {
            App::setLocale( ns()->option->get( 'ns_store_language', 'en' ) );
        }

        /**
         * when the default language is loaded,
         * we might dispatch an event that will load module
         * locale as well.
         */
        event( new LocaleDefinedEvent( App::getLocale() ) );

        return $next( $request );
    }
}
