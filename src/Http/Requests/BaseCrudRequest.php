<?php

namespace Ns\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Ns\Services\CrudService;

class BaseCrudRequest extends FormRequest
{
    public function getPlainData( $namespace, $entry = null )
    {
        $service = new CrudService;
        $resource = $service->getCrudInstance( $this->route( 'namespace' ) );

        return $resource->getPlainData( $this, $entry );
    }
}
