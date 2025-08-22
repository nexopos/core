<?php

/**
 * NexoPOS Controller
 *
 * @since  1.0
 **/

namespace Ns\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ns\Classes\JsonResponse;
use Ns\Http\Requests\ApplicationConfigRequest;
use Ns\Services\SetupService;

class SetupController extends Controller
{
    public function __construct( private SetupService $setup )
    {
        // ...
    }

    public function welcome( Request $request )
    {
        return view( 'ns::pages.setup.welcome', [
            'title' => __( 'Welcome — NexoPOS' ),
            'languages' => config( 'nexopos.languages' ),
            'lang' => $request->query( 'lang' ) ?: 'en',
        ] );
    }

    public function checkDatabase( Request $request )
    {
        return $this->setup->saveDatabaseSettings( $request );
    }

    public function checkDbConfigDefined( Request $request )
    {
        return $this->setup->testDBConnexion();
    }

    public function saveConfiguration( ApplicationConfigRequest $request )
    {
        return $this->setup->runMigration( $request->all() );
    }

    public function checkExistingCredentials()
    {
        try {
            if ( DB::connection()->getPdo() ) {
                /**
                 * We believe from here the app should update the .env file to ensure
                 * the APP_URL and others values are updated with the actual domain name.
                 */
                $this->setup->updateAppURL();

                return JsonResponse::success(
                    message: __( 'The database connection has been successfully established.' )
                );
            }
        } catch ( \Exception $e ) {
            return JsonResponse::error(
                message: $e->getMessage()
            );
        }
    }
}
