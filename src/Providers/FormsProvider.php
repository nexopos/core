<?php

namespace Ns\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Ns\Forms\POSAddressesForm;
use Ns\Forms\ProcurementForm;
use Ns\Forms\UserProfileForm;
use Ns\Services\ModulesService;
use ReflectionClass;
use TorMorten\Eventy\Facades\Events as Hook;

class FormsProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // ...
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Hook::addFilter( 'ns.forms', function ( $class, $identifier ) {
            switch ( $identifier ) {
                case 'ns.user-profile':
                    return new UserProfileForm;
                    break;
                case 'ns.procurement':
                    return new ProcurementForm;
                    break;
                case 'ns.pos-addresses':
                    return new POSAddressesForm;
                    break;
            }

            return $class;
        }, 10, 2 );

        /**
         * We'll scan the fields directory
         * and autoload the fields that has "AUTOLOAD" constant
         * set to true
         */
        $this->autoloadFields(
            path: __DIR__ . '/../Fields',
            classRoot: 'Ns\\Fields\\'
        );

        /**
         * Now for all the modules that are enabled we'll make sure
         * to load their fields if they are set to be autoloaded
         *
         * @var ModulesService
         */
        $moduleService = app()->make( ModulesService::class );

        $moduleService->getEnabledAndAutoloadedModules()->each( function ( $module ) {
            $module = (object) $module;

            $this->autoloadFields(
                path: Str::finish( $module->path, DIRECTORY_SEPARATOR ) . 'Fields',
                classRoot: 'Modules\\' . $module->namespace . '\\Fields\\'
            );

            $this->autoloadForms(
                path: Str::finish( $module->path, DIRECTORY_SEPARATOR ) . 'Forms',
                classRoot: 'Modules\\' . $module->namespace . '\\Forms\\'
            );
        } );
    }

    private function autoloadForms( $path, $classRoot )
    {
        if ( ! is_dir( $path ) ) {
            return;
        }

        $forms = scandir( $path );

        foreach ( $forms as $form ) {
            if ( in_array( $form, [ '.', '..' ] ) ) {
                continue;
            }

            // the file must be a .php file.
            if ( ! str_contains( $form, '.php' ) ) {
                continue;
            }

            $form = str_replace( '.php', '', $form );
            $form = $classRoot . $form;

            if ( class_exists( $form ) && method_exists( $form, 'getForm' ) ) {
                Hook::addFilter( 'ns.forms', function ( $class, $identifier ) use ( $form ) {
                    if ( $identifier === $form::IDENTIFIER ) {
                        return new $form;
                    }

                    return $identifier;
                }, 10, 2 );
            }
        }
    }

    private function autoloadFields( $path, $classRoot )
    {
        if ( ! is_dir( $path ) ) {
            return;
        }

        $fields = scandir( $path );

        foreach ( $fields as $field ) {
            if ( in_array( $field, [ '.', '..' ] ) ) {
                continue;
            }

            // the file must be a .php file.
            if ( ! str_contains( $field, '.php' ) ) {
                continue;
            }

            $field = str_replace( '.php', '', $field );
            $field = $classRoot . $field;

            $reflection = new ReflectionClass( $field );

            if ( class_exists( $field ) && $reflection->hasConstant( 'AUTOLOAD' ) && $field::AUTOLOAD && $reflection->hasConstant( 'IDENTIFIER' ) ) {
                Hook::addFilter( 'ns.fields', function ( $identifier ) use ( $field ) {
                    if ( $identifier === $field::IDENTIFIER ) {
                        return new $field;
                    }

                    return $identifier;
                } );
            }
        }
    }
}
