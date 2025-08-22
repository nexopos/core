<?php

/**
 * NexoPOS Controller
 *
 * @since  1.0
 **/

namespace Ns\Http\Controllers\Dashboard;

use Exception;
use Ns\Http\Controllers\DashboardController;
use Ns\Http\Requests\FormsRequest;
use Ns\Services\SettingsPage;
use TorMorten\Eventy\Facades\Events as Hook;

class FormsController extends DashboardController
{
    public function getForm( $resource )
    {
        /**
         * @var SettingsPage
         */
        $instance = Hook::filter( 'ns.forms', [], $resource );

        if ( ! $instance instanceof SettingsPage ) {
            throw new Exception( sprintf(
                '%s is not an instanceof "%s".',
                $resource,
                SettingsPage::class
            ) );
        }

        return $instance->getForm();
    }

    public function saveForm( FormsRequest $request, $resource, $identifier = null )
    {
        $instance = Hook::filter( 'ns.forms', [], $resource, $identifier );

        if ( ! $instance instanceof SettingsPage ) {
            throw new Exception( sprintf(
                '%s is not an instanceof "%s".',
                $resource,
                SettingsPage::class
            ) );
        }

        return $instance->saveForm( $request );
    }
}
