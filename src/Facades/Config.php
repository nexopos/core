<?php

namespace Ns\Facades;

use Illuminate\Support\Facades\Facade;
use Ns\Classes\Config as ClassesConfig;

class Config extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ClassesConfig::class;
    }
}
