<?php

namespace Ns\Services;

use Ns\Models\Option;
use Ns\Models\User;
use Ns\Models\UserAttribute;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DoctorService
{
    public function __construct( protected Command $command )
    {
        // ...
    }

    public function createUserAttribute(): array
    {
        User::get()->each( function ( User $user ) {
            $this->createAttributeForUser( $user );
        } );

        return [
            'status' => 'success',
            'message' => __( 'The user attributes has been updated.' ),
        ];
    }

    public function createAttributeForUser( User $user )
    {
        if ( ! $user->attribute instanceof UserAttribute ) {
            $attribute = new UserAttribute;
            $attribute->user_id = $user->id;
            $attribute->language = ns()->option->get( 'ns_store_language', 'en' );
            $attribute->theme = ns()->option->get( 'ns_default_theme', 'dark' );
            $attribute->save();
        }
    }

    public function fixDuplicateOptions()
    {
        $options = Option::get();
        $options->each( function ( $option ) {
            try {
                $option->refresh();
                if ( $option instanceof Option ) {
                    Option::where( 'key', $option->key )
                        ->where( 'id', '<>', $option->id )
                        ->delete();
                }
            } catch ( Exception $exception ) {
                // the option might be deleted, let's skip that.
            }
        } );
    }

    /**
     * useful to configure
     * session domain and sanctum stateful domains
     *
     * @return void
     */
    public function fixDomains()
    {
        /**
         * Set version to close setup
         */
        $domain = Str::replaceFirst( 'http://', '', url( '/' ) );
        $domain = Str::replaceFirst( 'https://', '', $domain );
        $withoutPort = explode( ':', $domain )[0];

        if ( ! env( 'SESSION_DOMAIN', false ) ) {
            ns()->envEditor->set( 'SESSION_DOMAIN', Str::replaceFirst( 'http://', '', $withoutPort ) );
        }

        if ( ! env( 'SANCTUM_STATEFUL_DOMAINS', false ) ) {
            ns()->envEditor->set( 'SANCTUM_STATEFUL_DOMAINS', collect( [ $domain, 'localhost', '127.0.0.1' ] )->unique()->join( ',' ) );
        }
    }

    public function clearTemporaryFiles()
    {
        $directories = Storage::disk( 'ns-modules-temp' )->directories();
        $deleted = collect( $directories )->filter( fn( $directory ) => Storage::disk( 'ns-modules-temp' )->deleteDirectory( $directory ) );

        $this->command->info( sprintf(
            __( '%s on %s directories were deleted.' ),
            count( $directories ),
            $deleted->count()
        ) );

        $files = Storage::disk( 'ns-modules-temp' )->files();
        $deleted = collect( $files )->filter( fn( $file ) => Storage::disk( 'ns-modules-temp' )->delete( $file ) );

        $this->command->info( sprintf(
            __( '%s on %s files were deleted.' ),
            count( $files ),
            $deleted->count()
        ) );
    }

    /**
     * Check if all symbolic links available in a directory are not broken
     * and delete the broken symbolic links
     */
    public function clearBrokenModuleLinks(): array
    {
        $dir = base_path( 'public/modules' );
        $files = scandir( $dir );
        $deleted = [];

        foreach ( $files as $file ) {
            if ( $file === '.' || $file === '..' ) {
                continue;
            }

            if ( is_link( $dir . '/' . $file ) ) {
                if ( ! file_exists( readlink( $dir . '/' . $file ) ) ) {
                    $deleted[] = $dir . '/' . $file;
                    unlink( $dir . '/' . $file );
                }
            }
        }

        return [
            'status' => 'success',
            'message' => sprintf(
                __( '%s links were deleted' ),
                count( $deleted )
            ),
        ];
    }

    /**
     * Purge removed migrations from the database
     * and keep only the existing ones
     *
     * @return string
     */
    public function purgeRemovedMigrations()
    {
        $existingMigrationFiles = Storage::disk( 'ns' )->allFiles( 'database/migrations' );
        $filesNames = collect( $existingMigrationFiles )->map( fn( $file ) => pathinfo( $file )[ 'filename' ] );

        $query = DB::table( 'migrations' )->whereNotIn( 'migration', $filesNames );
        $total = $query->count();
        $query->delete();

        return $this->command->info( __( '%s migrations were purged' ), $total );
    }
}
