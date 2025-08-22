<?php

namespace Ns\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Ns\Classes\Config as ClassesConfig;
use Ns\Classes\Hook;
use Ns\Services\CoreService;
use Ns\Services\CurrencyService;
use Ns\Services\DateService;
use Ns\Services\EnvEditor;
use Ns\Services\MathService;
use Ns\Services\MediaService;
use Ns\Services\MenuService;
use Ns\Services\NotificationService;
use Ns\Services\Options;
use Ns\Services\UpdateService;
use Ns\Services\UserOptions;
use Ns\Services\UsersService;
use Ns\Services\Validation;
use Ns\Services\WidgetService;
use TorMorten\Eventy\Events;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        include_once dirname( __FILE__ ) . '/../Services/HelperFunctions.php';

        $this->app->singleton( 'eventy', function () {
            return new Events;
        } );

        AliasLoader::getInstance()->alias( 'Hook', Hook::class );

        $this->app->singleton( Options::class, function () {
            return new Options;
        } );

        // // save Singleton for options
        $this->app->singleton( DateService::class, function () {
            $options = app()->make( Options::class );
            $timeZone = $options->get( 'ns_datetime_timezone', 'Europe/London' );

            config( ['app.timezone' => $timeZone ] );
            date_default_timezone_set( $timeZone );

            return new DateService( 'now', $timeZone );
        } );

        $this->app->singleton( MenuService::class, function () {
            return new MenuService;
        } );

        $this->app->singleton( EnvEditor::class, function () {
            return new EnvEditor( base_path( '.env' ) );
        } );

        // save Singleton for options
        $this->app->singleton( UserOptions::class, function () {
            return new UserOptions( Auth::id() );
        } );

        $this->app->singleton( WidgetService::class, function ( $app ) {
            return new WidgetService(
                $app->make( UsersService::class )
            );
        } );

        $this->app->singleton( Config::class, function () {
            return new ClassesConfig;
        } );

        $this->app->singleton( CoreService::class, function () {
            return new CoreService(
                currency: app()->make( CurrencyService::class ),
                update: app()->make( UpdateService::class ),
                date: app()->make( DateService::class ),
                notification: app()->make( NotificationService::class ),
                option: app()->make( Options::class ),
                math: app()->make( MathService::class ),
                envEditor: app()->make( EnvEditor::class ),
                mediaService: app()->make( MediaService::class ),
            );
        } );

        $this->app->bind( CurrencyService::class, function ( $app ) {
            $options = app()->make( Options::class );

            return new CurrencyService(
                0, [
                    'decimal_precision' => $options->get( 'ns_currency_precision', 0 ),
                    'decimal_separator' => $options->get( 'ns_currency_decimal_separator', ',' ),
                    'thousand_separator' => $options->get( 'ns_currency_thousand_separator', '.' ),
                    'currency_position' => $options->get( 'ns_currency_position', 'before' ),
                    'currency_symbol' => $options->get( 'ns_currency_symbol' ),
                    'currency_iso' => $options->get( 'ns_currency_iso' ),
                    'prefered_currency' => $options->get( 'ns_currency_prefered' ),
                ]
            );
        } );

        $this->app->singleton( Validation::class, function ( $app ) {
            return new Validation;
        } );

        $this->app->singleton( WidgetService::class, function ( $app ) {
            return new WidgetService(
                $app->make( UsersService::class )
            );
        } );
    }
}
