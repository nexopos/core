<?php

namespace Ns\Classes;

use Ns\Http\Middleware\CheckApplicationHealthMiddleware;
use Ns\Http\Middleware\CheckMigrationStatus;
use Ns\Http\Middleware\InstalledStateMiddleware;
use Ns\Services\ModulesService;
use Illuminate\Support\Facades\Route;

class ModuleRouting
{
    public static function register( array $routes )
    {
        /**
         * @var ModulesService $Modules
         */
        $modulesService = app()->make( ModulesService::class );

        foreach ( $modulesService->getEnabledAndAutoloadedModules() as $module ) {
            /**
             * will load all web.php file as dashboard routes.
             */
            if ( $module[ 'routes-file' ] !== false && in_array( 'web', $routes ) ) {
                self::mapModuleWebRoutes( $module );
            }

            /**
             * will load api.php file has api file
             */
            if ( $module[ 'api-file' ] !== false && in_array( 'api', $routes ) ) {
                self::mapModuleApiRoutes( $module );
            }

            /**
             * will load api.php file has api file
             */
            if ( $module[ 'domain-file' ] !== false && in_array( 'domain', $routes ) ) {
                self::mapModuleDomainRoutes( $module );
            }
        }
    }

    public static function mapModuleDomainRoutes( $module )
    {
        Route::domain( env( 'APP_URL' ) )
            ->middleware( [ 
                'web',
                InstalledStateMiddleware::class,
                CheckApplicationHealthMiddleware::class,
                CheckMigrationStatus::class,
            ] )
            ->namespace( 'Modules\\' . $module[ 'namespace' ] . '\Http\Controllers' )
            ->group( $module[ 'domain-file' ] );
    }

    public static function mapModuleWebRoutes( $module )
    {
        Route::middleware( [
                'web',
                InstalledStateMiddleware::class,
                CheckApplicationHealthMiddleware::class,
                CheckMigrationStatus::class 
            ] )
            ->namespace( 'Modules\\' . $module[ 'namespace' ] . '\Http\Controllers' )
            ->group( $module[ 'routes-file' ] );
    }

    public static function mapModuleApiRoutes( $module )
    {
        Route::prefix( 'api' )
            ->middleware( [ InstalledStateMiddleware::class, 'api' ] )
            ->namespace( 'Modules\\' . $module[ 'namespace' ] . '\Http\Controllers' )
            ->group( $module[ 'api-file' ] );
    }
}
