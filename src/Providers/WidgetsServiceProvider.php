<?php

namespace Ns\Providers;

use Illuminate\Support\ServiceProvider;
use Ns\Services\WidgetService;

class WidgetsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /**
         * @var WidgetService $widgetService
         */
        $widgetService = app()->make( WidgetService::class );

        $widgetService->bootWidgetsAreas();
    }
}
