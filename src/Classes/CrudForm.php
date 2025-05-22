<?php

namespace Ns\Classes;

class CrudForm extends Form
{
    public static function form( $main = null, $tabs = [] )
    {
        return compact( 'main', 'tabs' );
    }
}
