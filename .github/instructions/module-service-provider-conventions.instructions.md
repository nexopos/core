# NexoPOS Core - Module Service Provider Conventions

## Last Updated
2026-01-25

## Overview
This document outlines the proper conventions for creating ServiceProvider classes in NexoPOS Core modules. Following these conventions ensures modules integrate properly with the NexoPOS Core framework.

---

## ✅ What TO DO

### 1. Keep Service Provider Minimal
NexoPOS Core handles most module registration automatically. Your service provider should be lean:

```php
<?php

namespace Modules\YourModule\Providers;

use Illuminate\Support\ServiceProvider;

class YourModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Most services are auto-registered by NexoPOS Core
    }

    public function provides(): array
    {
        return [];
    }
}
```

### 3. Use NexoPOS Core Configuration
Access languages from NexoPOS Core config:

```php
// Get all supported languages
$languages = config('nexopos.languages');
// Returns: ['en' => [...], 'fr' => [...], ...]

// Get default language
$defaultLanguage = config('nexopos.language');
// Returns: 'en'

// Get language codes only
$locales = array_keys(config('nexopos.languages'));
// Returns: ['en', 'de', 'fr', 'es', 'it', 'ar', 'pt', 'tr', 'km', 'vi', 'sq']
```

### 4. Retrieve User Localization Preferences

When retrieving localization settings, always check the authenticated user's preference first, then fall back to the application default:

**Order of Priority:**
1. User's language attribute (if authenticated)
2. Application's default language setting

**Example Implementation:**
```php
use Illuminate\Support\Facades\Auth;
use Ns\Models\UserAttribute;

// Get user's preferred language
if (Auth::check()) {
    $userLanguage = UserAttribute::where('user_id', Auth::id())
        ->where('key', 'language') // Or your specific attribute key
        ->value('value');
}

// Fall back to application default
$language = $userLanguage ?? ns()->option->get('ns_store_language', 'en');
```

**Complete Localization Helper:**
```php
public function getUserLanguage(): string
{
    // Check authenticated user's preference
    if (Auth::check()) {
        $attribute = UserAttribute::where('user_id', Auth::id())
            ->where('key', 'language')
            ->first();
        
        if ($attribute && $attribute->value) {
            return $attribute->value;
        }
    }
    
    // Fall back to store default
    return ns()->option->get('ns_store_language', 'en');
}
```

---

## ❌ What NOT TO DO

### 1. Don't Merge Config Files
**❌ WRONG:**
```php
public function register(): void
{
    $this->mergeConfigFrom(
        __DIR__ . '/../Config/config.php',
        'yourmodule'
    );
}
```

**Why:** NexoPOS Core manages configuration centrally. Module-specific config files are usually unnecessary.

### 2. Don't Register Views
**❌ WRONG:**
```php
protected function registerViews(): void
{
    $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'YourModule');
}
```

**Why:** NexoPOS Core automatically discovers and registers module views.

### 3. Don't Register Migrations
**❌ WRONG:**
```php
public function boot(): void
{
    $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
}
```

**Why:** NexoPOS Core handles module migrations through its own migration system.

### 4. Don't Register Middleware
**❌ WRONG:**
```php
protected function registerMiddleware(): void
{
    $router = $this->app['router'];
    $router->aliasMiddleware('yourMiddleware', YourMiddleware::class);
    $router->pushMiddlewareToGroup('web', YourMiddleware::class);
}
```

**Why:** NexoPOS Core manages middleware registration. If you need custom middleware, register it through NexoPOS Core's middleware system, not in your service provider.

### 5. Don't Create Module-Specific Language Arrays
**❌ WRONG:**
```php
// In config/yourmodule.php
return [
    'supported_locales' => ['en', 'fr'],
    'default_locale' => 'en',
];

// In middleware
$supportedLocales = config('yourmodule.supported_locales');
```

**✅ CORRECT:**
```php
// Get languages from NexoPOS Core
$languages = config('nexopos.languages');
$supportedLocales = array_keys($languages);
$defaultLocale = config('nexopos.language', 'en');
```

---

## Real-World Example: NexoPlatform Module

### Before (Over-Engineered)
```php
<?php

namespace Modules\NexoPlatform\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\NexoPlatform\Http\Middleware\LocalizationMiddleware;

class NexoPlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ❌ Don't do this
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'nexoplatform'
        );
    }

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerViews();              // ❌ Don't do this
        $this->registerMiddleware();         // ❌ Don't do this
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations'); // ❌ Don't do this
    }

    protected function registerViews(): void
    {
        // ❌ Don't do this
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'NexoPlatform');
    }

    protected function registerMiddleware(): void
    {
        // ❌ Don't do this
        $router = $this->app['router'];
        $router->aliasMiddleware('localization', LocalizationMiddleware::class);
        $router->pushMiddlewareToGroup('web', LocalizationMiddleware::class);
    }
}
```

### After (Correct)
```php
<?php

namespace Modules\NexoPlatform\Providers;

use Illuminate\Support\ServiceProvider;

class NexoPlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Services are automatically registered by NexoPOS Core
    }

    public function boot(): void
    {
        // ✅ Only register JSON translations
        $this->registerTranslations();
    }

    protected function registerTranslations(): void
    {
        $langPath = __DIR__ . '/../Resources/lang';

        if (is_dir($langPath)) {
            $this->loadJsonTranslationsFrom($langPath);
        }
    }

    public function provides(): array
    {
        return [];
    }
}
```

---

## Key Principles

1. **Trust NexoPOS Core**: The framework handles most registration automatically
2. **Minimal Service Providers**: Only register what's truly necessary (usually just translations)
3. **Use Central Configuration**: Always use `config('nexopos.*')` instead of module-specific configs
4. **No Laravel Boilerplate**: Don't copy standard Laravel service provider patterns

---

## Related Documentation

- [Translation System](translation-system.md)
- [Module Structure](module-structure.md)
- [Configuration Management](configuration-management.md)

---

## Questions?

If you're unsure whether something should be in your service provider, the answer is probably **NO**. When in doubt, keep it minimal and let NexoPOS Core handle it.
