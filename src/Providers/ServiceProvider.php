<?php
namespace Ns\Providers;

use Illuminate\Support\ServiceProvider as CoreServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Ns\Classes\NsViteDirective;
use Ns\Console\Commands\CrudGeneratorCommand;
use Ns\Console\Commands\ExtractTranslation;
use Ns\Console\Commands\GenerateModuleCommand;
use Ns\Console\Commands\InstallCommand;
use Ns\Console\Commands\ModuleMigrations;
use Ns\Console\Commands\ModuleSymlinkCommand;
use Ns\View\Components\SessionMessage;
use Ns\Services\Helper;
use Ns\Events\LoadApiRouteEvent;

/**
 * Class Provider
 *
 * @package Ns\Providers
 */
class ServiceProvider extends CoreServiceProvider
{
    public function register()
    {
        /**
         * We need to check if the database is installed.
         * If it's the case, we can register the service providers.
         */
        $this->app->register( AppServiceProvider::class );
        
        if ( Helper::checkDatabaseExistence() ) {
            $this->app->register( AuthServiceProvider::class );
            $this->app->register( CrudServiceProvider::class );
            $this->app->register( EventServiceProvider::class );
            $this->app->register( FileSystemServiceProvider::class );
            $this->app->register( FormsProvider::class );
            $this->app->register( LocalizationServiceProvider::class );
            $this->app->register( ModulesServiceProvider::class );
            $this->app->register( RouteServiceProvider::class );
            $this->app->register( SettingsPageProvider::class );
            $this->app->register( WidgetsServiceProvider::class ); 
            
            $this->app->singleton( 'ns.installed', function () {
                return true;
            });
        } else {
            $this->app->singleton( 'ns.installed', function () {
                return false;
            });
        }

        define( 'NS_ROOT', __DIR__ . '/../../' );
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'ns');

        $this->loadJsonTranslationsFrom ( __DIR__ . '/../lang' );  

        /**
         * This groups all the things that are only needed when the app is running in console.
         */
        if ( $this->app->runningInConsole() ) {
            $this->publishesMigrations([
                __DIR__ . '/../../database/migrations' => database_path( 'migrations' ),
            ]);
    
            $this->publishes([
                __DIR__ . '/../../public' => public_path( 'vendor/ns' ),
            ], 'nexopos-assets' );
    
            $this->publishes([
                __DIR__ . '/../../config/nexopos.php' => config_path( 'nexopos.php' ),
                __DIR__ . '/../../config/medias.php' => config_path( 'medias.php' ),
            ], 'nexopos-config' );
            
            $this->publishes([
                __DIR__ . '/../../database/permissions' => database_path( 'permissions' ),
            ], 'nexopos-permissions' );

            $this->commands([
                InstallCommand::class,
                ExtractTranslation::class,
                GenerateModuleCommand::class,
                CrudGeneratorCommand::class,
                ModuleMigrations::class,
                ModuleSymlinkCommand::class,
            ]);
        }

        Blade::directive( 'nsvite', new NsViteDirective );

        Blade::directive( 'moduleViteAssets', function ( $expression ) {
            $params = explode( ',', $expression );
            $fileName = trim( $params[0], "'" );
            $module = trim( $params[1], " '" );

            return "<?php echo ns()->moduleViteAssets( \"{$fileName}\", \"{$module}\" ); ?>";
        } );

        Blade::component( 'session-message', SessionMessage::class );

        /**
         * As the API routes depends on Laravel Sanctum, which might not be loaded at this point,
         * We'll then inject an Event to the api.php that will then be triggered when the api.php file
         * is accessed by the app and therefore our API routes will be loaded.
         */
        Event::listen( LoadApiRouteEvent::class, function() {
            $this->loadRoutesFrom( __DIR__ . '/../../routes/api.php' );
        });

        Route::middleware( 'web' )->group( function() {
            $this->loadRoutesFrom( __DIR__ . '/../../routes/web.php' );
        });
    }
}
