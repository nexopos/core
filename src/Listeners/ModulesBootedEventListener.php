<?php
namespace Ns\Listeners;

use Ns\Events\ModulesBootedEvent;
use Ns\Events\ModulesLoadedEvent;
use Ns\Services\ModulesService;

class ModulesBootedEventListener
{
    public function __construct( public ModulesService $modulesService )
    {
        //
    }

    public function handle( ModulesBootedEvent $event )
    {
        // ...
    }
}