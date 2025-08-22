<?php

namespace Ns\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ns\Services\CrudService;
use stdClass;

class CrudAfterDeleteEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public CrudService $resource,
        public stdClass $model
    ) {
        // ...
    }
}
