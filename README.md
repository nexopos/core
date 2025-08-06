# NexoPOS Core
This package is a separation of the core features of NexoPOS from the POS related features. This gives all the necessary to start a lightweight app with yet proven features:

- User Authentication
- Roles Management
- Module Support
- Widget Support
- Settings Support

## Installation
This package is available on packagist and can be installed as a package on a regular laravel installation:

```composer require nexopos/core:dev-main```

You'll instruct composer to allow such project by editing the composer.json file with:

```json
"minimum-stability": "dev",
"prefer-stable": true
```

### Filesystem Configuration
The package includes a command to writethe  filesystem configuration to the filesystem.php. For that, you need to run the command:

```
php artisan ns:install --filesystem
```

### Publishing Service Provider
Before proceeding, we'll publish NexoPOS by using the command "vendor:publish".

```
php artisan vendor:publish
```
Note that you'll be asked to select the provider. Select `Provider: Ns\Providers\ServiceProvider`

#### Laravel Sanctum
As the project relies on Laravel Sanctum, you need to run this command to install (publish) Laravel Sanctum configuration.
Note that the package is already a dependency.

```
php artisan install:api
```

This will publish all the necessary for Sanctum to work. As NexoPOS Core performs API calls, we need to configure the Sanctum middleware that will ensure all frontend requests are Stateful. This will be performed on the app.php located on the "bootstrap" directory.

```php
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestAreStateful;
use Ns\Http\Middleware\ApiMiddleware;

// ...
->withMiddleware( function( Middleware $middleware ) {
    $middleware->statefulApi();
    $middleware->group( 'api', [
        EnsureFrontendRequestsAreStateful::class,
        ApiMiddleware::class,
    ]);
})
// ...
```

### Core Routing Configuration
Similarily to Laravel Sanctum, NexoPOS Core needs some files to be published. Note that here, some of the existing file will be edit by the package as
it needs it to work properly. 

The impacted files are:

- config/filesystems.php
- routes/api.php

```
php artisan ns:install --routes
```
This commands will perform two things:

- It will publish the filesystem required to the filesystems.php
- It will update your api.php to trigger an event.

## Authentication

### Sanctum Configuration
NexoPOS Core uses it's own implementation of authentication. While it's created on top of laravel, it provides more features. Therefore, it's recommended to change the model provider on the config/auth.php. If you're using Laravel 12, you only need to set it using "AUTH_MODEL" on the environment file.

```
AUTH_MODEL = Ns\Models\User
```

Define the default authentication route. You'll need to add on the app.php on the bootstrap directory where unauthenticated users should be redirected. 

```php
// ...
->withMiddleware(function (Middleware $middleware) {
        // ...
        $middleware->redirectGuestsTo( fn() => route( 'ns.login' ) );
        // ...
    })
// ...
```

- Sanctum (publish vendors)

## Modules

### PSR-4 Autoloading

As we've exported core feauture of NexoPOS, modules are supported. However, in order for the modules files to be automatically loaded, you need to edit the composer.json file of the Laravel project. On The "autoload" entry, make sure to add on the "psr-4" entry the following:

```
"Modules\\": "modules/"
```

### Routing Registration
Modules comes with routes. But by default, Laravel will not load their route. Therefore, we need to instruct Laravel to load the module route while it's loading the application route. For that, on the app.php file located on the bootstrap folder, we'll add a "using" callback to the method "withRouting".

```php
use Ns\Classes\ModuleRouting;
use Illuminate\Support\Facades\Route;

// ...
->withRouting( 
    // ...
    // web: __DIR__.'/../routes/web.php',
    // api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    using: function() {
        Route::middleware( 'api' )
                ->prefix( 'api' )
                ->group( base_path( 'routes/api.php' ) );

        Route::middleware( 'web' )
            ->group( base_path( 'routes/web.php' ) );

        ModuleRouting::register([ 'web', 'api' ]);
    }
)
// ...
```

Note that if you use "using", then any load of web.php file or api.php file set as parameter of withRouting, will be ignored. That's the reason why we've imported the api.php and web.php file within the anonymous function.


## Socket Support
We've created a custom approach for working with Sockets on NexoPOS. The idea here is that when the user logs in, we'll create an access token that will be stored on the personnal_access_token table and set on the session. 

```php
public function handle( Validated $event)
{
    $token = $event->user->createToken( 'token', ['*'], now()->addWeek() )->plainTextToken;

    session()->put( 'token', $token );
}
```

This token is then used to authenticate the socket connexion.
