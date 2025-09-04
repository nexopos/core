---
applyTo: '**'
---

# Module Generation in NexoPOS Core

NexoPOS Core provides a command to generate new modules with a complete Laravel-like structure.

## Command Usage

```bash
php artisan make:module
```

### Options
- `--force`: Overwrite existing module if it exists

## Interactive Module Creation

The command will prompt for:

1. **Module Namespace**: The namespace for the module (e.g., `MyModule`)
2. **Module Name**: Human-readable name
3. **Author Name**: Module author
4. **Description**: Short description of the module
5. **Version**: Automatically set to 1.0

## Generated Module Structure

```
modules/ModuleName/
├── config.xml                 # Module configuration
├── vite.config.js             # Frontend build configuration
├── Controllers/               # HTTP controllers
├── Events/                    # Custom events
├── Jobs/                      # Queue jobs
├── Listeners/                 # Event listeners
├── Middleware/                # HTTP middleware
├── Migrations/                # Database migrations
├── Models/                    # Eloquent models
├── Providers/                 # Service providers
│   └── ModuleServiceProvider.php
├── Requests/                  # Form request validation
├── Resources/                 # Frontend assets
│   ├── css/
│   ├── js/
│   └── ts/
├── Routes/                    # Route definitions
│   ├── web.php               # Web routes
│   └── api.php               # API routes
├── Settings/                  # Configuration pages
├── Views/                     # Blade templates
│   └── index.blade.php
└── Widgets/                   # Dashboard widgets
```

## Module Configuration (config.xml)

The generated `config.xml` file contains:

```xml
<?xml version="1.0"?>
<module version="1.0">
    <namespace>ModuleName</namespace>
    <name>Module Name</name>
    <author>Author Name</author>
    <description>Module description</description>
    <version>1.0</version>
    <autoload>true</autoload>
</module>
```

## Generated Service Provider

Each module includes a base service provider:

```php
<?php
namespace Modules\ModuleName\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleNameServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
    }

    public function boot()
    {
        // Boot services
    }
}
```

## Automatic Features

- **Route Discovery**: `web.php` and `api.php` are automatically loaded
- **Migration Discovery**: Migrations are automatically discovered
- **View Discovery**: Views are accessible via `ModuleName::view-name`
- **Service Provider Registration**: Main service provider is auto-registered
- **Asset Compilation**: Vite configuration for frontend assets

## Development Workflow

1. Generate module: `php artisan make:module`
2. Create migrations: Add files to `Migrations/` directory
3. Run migrations: `php artisan module:migrate ModuleName`
4. Build assets: Configure `vite.config.js` and run build process
5. Develop features: Add controllers, models, views as needed

## Best Practices

- Use PSR-4 autoloading conventions
- Follow Laravel naming conventions
- Import classes with `use` statements
- Use module namespace for localization: `__m('text', 'ModuleName')`
- Prefix CSS classes appropriately to avoid conflicts
