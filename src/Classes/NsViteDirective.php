<?php

namespace Ns\Classes;

class NsViteDirective
{
    public function __invoke( $expression )
    {
        $content = file_get_contents( __DIR__ . '/../../resources/views/vite.blade.php' );
        $content = str_replace( "'{{ expression }}'", $expression, $content );

        return $content;
    }
}
