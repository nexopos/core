---
applyTo: '**'
---

# Service Providers in NexoPOS Core

Service providers in NexoPOS Core modules work like standard Laravel service providers but with additional capabilities for registering module-specific services, filters, and components.

## Creating a Service Provider

Service providers are stored in the `Providers/` directory and extend Laravel's `ServiceProvider`:

```php
<?php
namespace Modules\ModuleName\Providers;

use Illuminate\Support\ServiceProvider;
use Ns\Classes\Hook;
use Ns\Services\WidgetService;
use Modules\ModuleName\Services\ModuleService;
use Modules\ModuleName\Widgets\ModuleWidget;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Register module services
        $this->app->singleton(ModuleService::class, function ($app) {
            return new ModuleService();
        });

        // Register other bindings
        $this->app->bind('module.helper', function ($app) {
            return new \Modules\ModuleName\Helpers\ModuleHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Register widgets
        $this->registerWidgets();
        
        // Register hooks and filters
        $this->registerHooks();
        
        // Register view composers
        $this->registerViewComposers();
        
        // Register event listeners (optional - auto-discovery is preferred)
        // $this->registerEventListeners();
    }

    /**
     * Register module widgets
     */
    private function registerWidgets()
    {
        $widgetService = app()->make(WidgetService::class);
        $widgetService->registerWidgets([
            ModuleWidget::class,
            // Add more widgets here
        ]);
    }

    /**
     * Register hooks and filters
     */
    private function registerHooks()
    {
        // Add a filter to modify something
        Hook::addFilter('ns.dashboard.menus', function ($menus) {
            $menus[] = [
                'label' => __m('Module Menu', 'ModuleName'),
                'href' => url('/module/dashboard'),
                'icon' => 'la-cog'
            ];
            return $menus;
        });
    }

    /**
     * Register view composers
     */
    private function registerViewComposers()
    {
        view()->composer('ModuleName::layouts.app', function ($view) {
            $view->with('moduleData', app(ModuleService::class)->getLayoutData());
        });

        view()->composer('ModuleName::dashboard.*', function ($view) {
            $view->with('dashboardStats', app(ModuleService::class)->getDashboardStats());
        });
    }
}
```

## Widget Registration Service Provider

Create a dedicated provider for widgets:

```php
<?php
namespace Modules\ModuleName\Providers;

use Illuminate\Support\ServiceProvider;
use Ns\Services\WidgetService;
use Modules\ModuleName\Widgets\StatsWidget;
use Modules\ModuleName\Widgets\ActivityWidget;
use Modules\ModuleName\Widgets\ReportsWidget;

class WidgetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $widgetService = app()->make(WidgetService::class);
        $widgetService->registerWidgets([
            StatsWidget::class,
            ActivityWidget::class,
            ReportsWidget::class,
        ]);
    }
}
```

## Hook Usage Examples

### Adding Menu Items

```php
Hook::addFilter('ns.dashboard.menus', function ($menus) {
    $menus[] = [
        'label' => __m('My Module', 'ModuleName'),
        'href' => url('/my-module'),
        'icon' => 'la-puzzle-piece',
        'permission' => 'modulename.access'
    ];
    return $menus;
});
```

## Common Hook Points

### Dashboard Hooks
- `ns.dashboard.menus` - Add menu items

### General Hooks
- `ns.before.view.render` - Process before view rendering
- `ns.after.module.loaded` - Process after module is loaded
- `ns.system.settings` - Add system settings

## Auto-Discovery

NexoPOS Core automatically discovers:
- **Service Providers**: Main module service provider is auto-registered
- **Event Listeners**: Listeners are automatically discovered
- **Routes**: `web.php` and `api.php` are automatically loaded
- **Views**: Views are automatically registered with module namespace

## Best Practices

1. **Single Responsibility**: Create focused service providers for specific concerns
2. **Use Hooks Wisely**: Don't overuse hooks; prefer standard Laravel patterns
3. **Lazy Loading**: Register services lazily when possible
4. **Documentation**: Document your hooks and filters
5. **Backwards Compatibility**: Be careful when modifying existing hooks
6. **Performance**: Consider the performance impact of hooks and filters
