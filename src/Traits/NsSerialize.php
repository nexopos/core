<?php

namespace Ns\Traits;

use Modules\NsMultiStore\Models\Store;
use Ns\Events\JobBeforeSerializeEvent;
use Ns\Jobs\Middleware\UnserializeMiddleware;

trait NsSerialize
{
    public $attributes;

    public Store $store;

    protected function prepareSerialization()
    {
        JobBeforeSerializeEvent::dispatch( $this );
    }

    public function middleware()
    {
        return [
            UnserializeMiddleware::class,
        ];
    }
}
