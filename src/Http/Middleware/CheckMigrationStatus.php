<?php

namespace Ns\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ns\Events\AfterMigrationStatusCheckedEvent;
use Ns\Services\Helper;
use Ns\Services\ModulesService;

class CheckMigrationStatus
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle( Request $request, Closure $next )
    {
        if ( ns()->update->getMigrations()->count() > 0 ) {
            session( [ 'after_update' => url()->current() ] );

            return redirect( nsRoute( 'ns.database-update' ) );
        }

        if ( Helper::installed() ) {
            /**
             * @var ModulesService $module
             */
            $module = app()->make( ModulesService::class );
            $modules = collect( $module->getEnabledAndAutoloadedModules() );
            $total = $modules->filter( fn( $module ) => count( $module[ 'migrations' ] ) > 0 );

            if ( $total->count() > 0 ) {
                return redirect( nsRoute( 'ns.database-update' ) );
            }
        }

        AfterMigrationStatusCheckedEvent::dispatch( $next, $request );

        return $next( $request );
    }
}
