<?php

namespace Ns\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ns\Models\UserRoleRelation;

class UserRoleRelationAfterCreatedEvent
{
    use Dispatchable, SerializesModels;

    public $userRoleRelation;

    public function __construct( UserRoleRelation $userRoleRelation )
    {
        $this->userRoleRelation = $userRoleRelation;
    }
}
