<?php

namespace Ns\Events;

use Ns\Models\UserRoleRelation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRoleRelationAfterUpdatedEvent
{
    use Dispatchable, SerializesModels;

    public $userRoleRelation;

    public function __construct(UserRoleRelation $userRoleRelation)
    {
        $this->userRoleRelation = $userRoleRelation;
    }
}