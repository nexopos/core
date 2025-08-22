<?php

namespace Ns\Events;

use App\Models\User as ModelsUser;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ns\Models\User;

class AfterSuccessfulLoginEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct( public User|ModelsUser $user )
    {
        //
    }
}
