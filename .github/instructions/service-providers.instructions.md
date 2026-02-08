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
        // Register filters using dedicated filter classes
        Hook::addFilter('ns.dashboard.menus', [DashboardMenusFilter::class, 'addModuleMenu']);
        Hook::addFilter('ns.crud.form', [CrudFormFilter::class, 'modifyForm']);
        Hook::addFilter('ns.settings.form', [SettingsFormFilter::class, 'addFields']);
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

## Filter Classes

### Filter Directory Structure

Filters should be organized in a `Filters/` directory within your module, grouped by their usage:

```
Modules/ModuleName/
└── Filters/
    ├── Crud/
    │   ├── ProductCrudFilter.php
    │   └── OrderCrudFilter.php
    ├── Views/
    │   ├── DashboardViewFilter.php
    │   └── HeaderViewFilter.php
    └── Settings/
        └── GeneralSettingsFilter.php
```

### Creating Filter Classes

Filters are used primarily for filtering input and mutating data. Always use dedicated filter classes instead of inline closures.

**Syntax:**
```php
Hook::addFilter('hook.name', [FilterClass::class, 'methodName']);
```

### Example: Dashboard Menu Filter

**File: `Filters/Views/DashboardMenusFilter.php`**

```php
<?php
namespace Modules\ModuleName\Filters\Views;

class DashboardMenusFilter
{
    /**
     * Add module menu items to dashboard
     *
     * @param array $menus
     * @return array
     */
    public static function addModuleMenu($menus)
    {
        $menus[] = [
            'label' => __m('My Module', 'ModuleName'),
            'href' => url('/my-module'),
            'icon' => 'la-puzzle-piece',
            'permission' => 'modulename.access'
        ];
        
        return $menus;
    }
}
```

### Example: CRUD Form Filter

**File: `Filters/Crud/ProductCrudFilter.php`**

```php
<?php
namespace Modules\ModuleName\Filters\Crud;

class ProductCrudFilter
{
    /**
     * Modify product CRUD form
     *
     * @param array $form
     * @param string $identifier
     * @return array
     */
    public static function modifyForm($form, $identifier)
    {
        // Only modify if it's the product CRUD
        if ($identifier !== 'ns.products') {
            return $form;
        }
        
        // Add custom field to the form
        if (isset($form['tabs']['general']['fields'])) {
            $form['tabs']['general']['fields'][] = [
                'name' => 'custom_field',
                'label' => __m('Custom Field', 'ModuleName'),
                'type' => 'text',
                'description' => __m('Add custom data', 'ModuleName'),
            ];
        }
        
        return $form;
    }
}
```

### Example: Settings Filter

**File: `Filters/Settings/GeneralSettingsFilter.php`**

```php
<?php
namespace Modules\ModuleName\Filters\Settings;

use Ns\Classes\FormInput;

class GeneralSettingsFilter
{
    /**
     * Add custom fields to general settings
     *
     * @param array $fields
     * @return array
     */
    public static function addFields($fields)
    {
        $fields[] = FormInput::text(
            name: 'modulename_api_key',
            value: ns()->option->get('modulename_api_key'),
            label: __m('API Key', 'ModuleName'),
            description: __m('Enter your API key for integration', 'ModuleName')
        );
        
        $fields[] = FormInput::switch(
            name: 'modulename_enable_feature',
            value: ns()->option->get('modulename_enable_feature', false),
            label: __m('Enable Feature', 'ModuleName'),
            description: __m('Enable or disable the feature', 'ModuleName')
        );
        
        return $fields;
    }
}
```

### Example: View Data Filter

**File: `Filters/Views/DashboardDataFilter.php`**

```php
<?php
namespace Modules\ModuleName\Filters\Views;

class DashboardDataFilter
{
    /**
     * Add custom data to dashboard view
     *
     * @param array $data
     * @return array
     */
    public static function addCustomData($data)
    {
        $data['module_stats'] = [
            'total_items' => 150,
            'active_users' => 45,
            'revenue' => 25000,
        ];
        
        return $data;
    }
}
```

### Registering Multiple Filters

**In Service Provider:**

```php
use Modules\ModuleName\Filters\Crud\ProductCrudFilter;
use Modules\ModuleName\Filters\Crud\OrderCrudFilter;
use Modules\ModuleName\Filters\Views\DashboardMenusFilter;
use Modules\ModuleName\Filters\Views\DashboardDataFilter;
use Modules\ModuleName\Filters\Settings\GeneralSettingsFilter;

private function registerHooks()
{
    // Dashboard filters
    Hook::addFilter('ns.dashboard.menus', [DashboardMenusFilter::class, 'addModuleMenu']);
    Hook::addFilter('ns.dashboard.data', [DashboardDataFilter::class, 'addCustomData']);
    
    // CRUD filters
    Hook::addFilter('ns.crud.form', [ProductCrudFilter::class, 'modifyForm']);
    Hook::addFilter('ns.crud.columns', [OrderCrudFilter::class, 'addColumns']);
    
    // Settings filters
    Hook::addFilter('ns.settings.general.fields', [GeneralSettingsFilter::class, 'addFields']);
}
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

1. **Use Dedicated Filter Classes**: Always create separate filter classes instead of inline closures
   - ✅ Good: `Hook::addFilter('hook', [FilterClass::class, 'method'])`
   - ❌ Bad: `Hook::addFilter('hook', function($data) { ... })`

2. **Organize Filters by Usage**: Group filters in subdirectories (`Crud/`, `Views/`, `Settings/`)

3. **Use Static Methods**: Filter callback methods should typically be static

4. **Document Filter Methods**: Add clear PHPDoc comments explaining parameters and return types

5. **Return Modified Data**: Filters must always return the modified data

6. **Check Context**: Verify identifiers before modifying (e.g., check CRUD identifier)

7. **Single Responsibility**: Each filter class should handle one specific type of filtering

8. **Descriptive Naming**: Use clear names that indicate what the filter does
   - `ProductCrudFilter` - Filters for product CRUD
   - `DashboardMenusFilter` - Filters for dashboard menus
   - `GeneralSettingsFilter` - Filters for general settings

9. **Use Hooks Wisely**: Don't overuse hooks; prefer standard Laravel patterns when possible

10. **Performance**: Consider the performance impact of filters, especially in loops
