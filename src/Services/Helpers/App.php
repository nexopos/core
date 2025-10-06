<?php

namespace Ns\Services\Helpers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ns\Classes\Hook;

trait App
{
    /**
     * Is installed
     *
     * @return bool
     */
    public static function installed()
    {
        return app()->make( 'ns.installed' );
    }

    public static function checkDatabaseExistence()
    {
        try {
            if ( DB::connection()->getPdo() ) {
                return Schema::hasTable( 'options' ) && Schema::hasTable( 'roles' );
            }

            return false;

        } catch ( \Exception $e ) {
            return false;
        }
    }

    public static function pageTitle( $string )
    {
        $storeName = ns()->option->get( 'ns_store_name' ) ?: 'NexoPOS';

        return sprintf(
            Hook::filter( 'ns-page-title', __( '%s — %s' ) ),
            $string,
            $storeName
        );
    }

    public static function tableHasIndex( $tableName, $indexName )
    {
        $driver = DB::getDriverName();
        $tableName = self::getPrefixedTableName( $tableName );

        switch ( $driver ) {
            case 'mysql':
                return self::hasIndexMySQL( $tableName, $indexName );

            case 'sqlite':
                return self::hasIndexSQLite( $tableName, $indexName );

            case 'pgsql':
                return self::hasIndexPostgres( $tableName, $indexName );

            default:
                throw new Exception( "Unsupported database driver: {$driver}" );
        }
    }

    public static function hasIndexMySQL( string $tableName, string $indexName )
    {
        $result = DB::select( "
            SHOW INDEX FROM {$tableName} WHERE Key_name = ?
        ", [$indexName] );

        return ! empty( $result );
    }

    public static function hasIndexSQLite( string $tableName, string $indexName )
    {
        $result = DB::select( "
            SELECT name
            FROM sqlite_master
            WHERE type = 'index'
            AND tbl_name = ?
            AND name = ?
        ", [$tableName, $indexName] );

        return ! empty( $result );
    }

    public static function hasIndexPostgres( string $tableName, string $indexName )
    {
        $result = DB::select( '
            SELECT COUNT(*)
            FROM pg_indexes
            WHERE tablename = ?
            AND indexname = ?
        ', [$tableName, $indexName] );

        return $result[0]->count > 0;
    }

    public static function getPrefixedTableName( string $tableName )
    {
        $prefix = DB::getTablePrefix();

        return $prefix . $tableName;
    }

    /**
     * Checks if the "back" query parameter
     * has a valid URL otherwise uses the
     * previous URL stored on the session.
     */
    public static function getValidPreviousUrl( Request $request ): string
    {
        if ( $request->has( 'back' ) ) {
            $backUrl = $request->query( 'back' );
            $parsedUrl = parse_url( $backUrl );
            $host = $parsedUrl['host'];

            if ( filter_var( $backUrl, FILTER_VALIDATE_URL ) && $host === parse_url( env( 'APP_URL' ), PHP_URL_HOST ) ) {
                return urldecode( $backUrl );
            }
        }

        return url()->previous();
    }

    /**
     * Swap arguments in a string
     * 
     * @param string $string
     * @param array $arguments
     * @return string
     */
    public static function swapArguments( $string, $arguments = [] )
    {
        // we'll replace any template argument by it's value
        // the template looks like {argument}

        foreach ( $arguments as $key => $value ) {
            $string = str_replace( '{' . $key . '}', $value, $string );
        }

        return $string;
    }
}
