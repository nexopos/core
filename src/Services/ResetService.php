<?php

namespace Ns\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Ns\Classes\Hook;
use Ns\Classes\Schema;
use Ns\Events\AfterHardResetEvent;
use Ns\Events\BeforeHardResetEvent;

class ResetService
{
    public function softReset()
    {
        $tables = Hook::filter( 'ns-wipeable-tables', [
            'medias',
            'notifications',
        ] );

        foreach ( $tables as $table ) {
            if ( Hook::filter( 'ns-reset-table', $table ) !== false && Schema::hasTable( Hook::filter( 'ns-reset-table', $table ) ) ) {
                DB::table( Hook::filter( 'ns-table-name', $table ) )->truncate();
            }
        }

        return [
            'status' => 'success',
            'message' => __( 'The table has been truncated.' ),
        ];
    }

    /**
     * Will completely wipe the database
     * forcing a new installation to be made
     */
    public function hardReset(): array
    {
        BeforeHardResetEvent::dispatch();

        /**
         * this will only apply clearing all tables
         * when we're not using sqlite.
         */
        if ( env( 'DB_CONNECTION' ) !== 'sqlite' ) {
            $tables = DB::select( 'SHOW TABLES' );

            foreach ( $tables as $table ) {
                $table_name = array_values( (array) $table )[0];
                DB::statement( 'SET FOREIGN_KEY_CHECKS = 0' );
                DB::statement( "DROP TABLE `$table_name`" );
                DB::statement( 'SET FOREIGN_KEY_CHECKS = 1' );
            }
        } else {
            file_put_contents( database_path( 'database.sqlite' ), '' );
        }

        Artisan::call( 'key:generate', [ '--force' => true ] );
        Artisan::call( 'ns:cookie generate' );

        exec( 'rm -rf public/storage' );

        AfterHardResetEvent::dispatch();

        return [
            'status' => 'success',
            'message' => __( 'The database has been wiped out.' ),
        ];
    }

    public function handleCustom( $data )
    {
        /**
         * @var string $mode
         * @var bool   $create_sales
         * @var bool   $create_procurements
         */
        extract( $data );

        return Hook::filter( 'ns-handle-custom-reset', [
            'status' => 'error',
            'message' => __( 'No custom handler for the reset "' . $mode . '"' ),
        ], $data );
    }
}
