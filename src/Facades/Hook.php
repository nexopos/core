<?php

namespace Ns\Facades;

use Illuminate\Support\Facades\Facade;
use Ns\Classes\Hook as ClassesHook;

class Hook extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ClassesHook::class;
    }
}
