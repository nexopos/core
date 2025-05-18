<?php

namespace Ns\Settings;

use Ns\Classes\FormInput;
use Ns\Classes\Hook;
use Ns\Classes\Output;
use Ns\Classes\SettingForm;
use Ns\Events\RenderFooterEvent;
use Ns\Services\Helper;
use Ns\Services\SettingsPage;
use Illuminate\Support\Facades\Event;

class ResetSettings extends SettingsPage
{
    const IDENTIFIER = 'reset';

    const AUTOLOAD = true;

    protected $form;

    public function __construct()
    {
        $this->form = SettingForm::form(
            title: __( 'Reset' ),
            description: __( 'Wipes and Reset the database.' ),
            tabs: SettingForm::tabs(
                SettingForm::tab(
                    identifier: 'reset',
                    label: __( 'Reset' ),
                    fields: SettingForm::fields(
                        FormInput::select(
                            name: 'mode',
                            label: __( 'Mode' ),
                            validation: 'required',
                            options: Helper::kvToJsOptions( Hook::filter( 'ns-reset-options', [
                                'wipe_all' => __( 'Wipe All' ),
                                'wipe_plus_grocery' => __( 'Wipe Plus Grocery' ),
                            ] ) ),
                            description: __( 'Choose what mode applies to this demo.' ),
                        ),
                        FormInput::checkbox(
                            name: 'create_sales',
                            label: __( 'Create Sales (needs Procurements)' ),
                            value: 1,
                            description: __( 'Set if the sales should be created.' ),
                        ),
                        FormInput::checkbox(
                            name: 'create_procurements',
                            label: __( 'Create Procurements' ),
                            value: 1,
                            description: __( 'Will create procurements.' ),
                        )
                    )
                )
            )
        );
    }

    public function beforeRenderForm()
    {
        Event::listen( RenderFooterEvent::class, function( $event ) {
            $event->output->addView( 'ns::pages.dashboard.settings.reset-footer' );
        });
    }
}
