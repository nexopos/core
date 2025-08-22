<?php

namespace Ns\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiMiddlewareTriggeredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct( public $request )
    {
        // ...
    }
}
