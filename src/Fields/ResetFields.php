<?php

namespace Ns\Fields;

use Ns\Classes\Hook;
use Ns\Models\Unit;
use Ns\Services\FieldsService;
use Ns\Services\Helper;

class ResetFields extends FieldsService
{
    /**
     * The unique identifier of the form
    **/
    const IDENTIFIER = 'ns.reset';

    /**
     * Will ensure the fields are automatically 
     * loaded
    **/
    const AUTOLOAD = true;

    public function get( ?Unit $model = null )
    {
        $this->fields = [
            [
                'name' => 'mode',
                'label' => __( 'Mode' ),
                'validation' => 'required',
                'type' => 'select',
                'options' => Helper::kvToJsOptions( Hook::filter( 'ns-reset-options', [
                    'wipe_all' => __( 'Wipe All' ),
                    'wipe_plus_grocery' => __( 'Wipe Plus Grocery' ),
                ] ) ),
                'description' => __( 'Choose what mode applies to this demo.' ),
            ], [
                'name' => 'create_sales',
                'label' => __( 'Create Sales (needs Procurements)' ),
                'type' => 'checkbox',
                'value' => 1,
                'description' => __( 'Set if the sales should be created.' ),
            ], [
                'name' => 'create_procurements',
                'label' => __( 'Create Procurements' ),
                'type' => 'checkbox',
                'value' => 1,
                'description' => __( 'Will create procurements.' ),
            ],
        ];

        return $this->fields;
    }
}
