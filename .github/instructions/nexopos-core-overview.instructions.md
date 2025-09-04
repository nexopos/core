---
applyTo: '**'
---

# NexoPOS Core Framework Instructions

NexoPOS Core is a decoupled version of NexoPOS that removes all POS features but keeps the necessary foundation to create any type of application. It's built on top of TailwindCSS 4, Vue 3, and Vite.

## Key Features

- **Module System**: Create modular applications with automatic discovery
- **Widget System**: Build dashboard widgets with Vue 3 components
- **Settings Management**: Configuration forms with tabs and validation
- **Form Builder**: Create dynamic forms with validation
- **Field System**: Simple field definitions for basic forms
- **Event System**: Laravel events with automatic listener discovery
- **Service Providers**: Modular service registration
- **Built-in Assets**: Pre-configured frontend tools and utilities

## Global Frontend Assets

The framework provides several global objects and utilities:

### HTTP Client (`nsHttpClient`)
- Methods: `get()`, `post()`, `put()`, `delete()`
- Returns RxJS subscriptions (use `.subscribe()`, not async/await)
- Direct response format (no wrapper objects)

### Popup System
- `nsAlertPopup`: Warning dialogs
- `nsPromptPopup`: Input dialogs
- `nsConfirmPopup`: Confirmation dialogs

### Notifications
- `nsSnackBar`: Toast messages (success, error, warning, info)
- `nsNotice`: Elaborate notifications with actions

### Vue Components
- `nsExtraComponents`: Global component registry
- `defineComponent`: Vue 3 component definition helper

## Directory Structure

Generated modules follow Laravel conventions with these key directories:
- `Providers/`: Service providers
- `Controllers/`: HTTP controllers
- `Models/`: Eloquent models (extend `Ns\Models\NsModel`)
- `Widgets/`: Dashboard widgets
- `Settings/`: Configuration pages
- `Events/`: Custom events
- `Listeners/`: Event listeners
- `Jobs/`: Queue jobs
- `Middleware/`: HTTP middleware
- `Requests/`: Form requests
- `Views/`: Blade templates
- `Routes/`: Route definitions (`web.php`, `api.php`)
- `Migrations/`: Database migrations

## View Loading

All module views are prefixed with the module namespace:

```php
return view('MyModule::index'); // loads modules/MyModule/Views/index.blade.php
```

## Models

- Extend `Ns\Models\NsModel` for module-specific models
- Use `Illuminate\Database\Eloquent\Model` for shared resources like Users
- NsModel allows other modules to filter table names

## Installation Commands

```bash
# Generate a new module
php artisan make:module

# Run module migrations
php artisan module:migrate {module}

# Generate CRUD for a module
php artisan make:crud {module}
```

This framework provides a solid foundation for building modular Laravel applications with a rich frontend experience.
