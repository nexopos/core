<?php

namespace Ns\Classes;

class SettingForm extends Form
{
    public static function form( $title, $description = '', $tabs = [] )
    {
        return compact( 'title', 'description', 'tabs' );
    }
}
