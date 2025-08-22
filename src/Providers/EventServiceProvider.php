<?php

namespace Ns\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Ns\Services\ModulesService;

class EventServiceProvider extends ServiceProvider
{
    protected $subscribe = [];

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        /**
         * @var ModulesService
         */
        $modulesServices = app()->make( ModulesService::class );

        $paths = $modulesServices->getEnabledAndAutoloadedModules()->map( function ( $module ) {
            return base_path( 'modules' . DIRECTORY_SEPARATOR . $module[ 'namespace' ] . DIRECTORY_SEPARATOR . 'Listeners' );
        } )
            ->values()
            ->toArray();

        return $paths;
    }

    public function shouldDiscoverEvents()
    {
        return true;
    }

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // ...
    }
}
