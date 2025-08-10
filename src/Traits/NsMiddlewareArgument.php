<?php

namespace Ns\Traits;

trait NsMiddlewareArgument
{
    public static function arguments( string|array $arguments )
    {
        if ( is_array( $arguments ) ) {
            return collect( $arguments )->map( fn( $argument ) => self::class . ':' . $argument )->toArray();
        } else {
            return self::class . ':' . $arguments;
        }
    }

    public static function any( array $arguments )
    {
        return self::class . ':any:' . collect( $arguments )->join( '|' );
    }
}
