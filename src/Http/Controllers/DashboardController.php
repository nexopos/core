<?php

/**
 * NexoPOS Controller
 *
 * @since  1.0
 **/

namespace Ns\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Ns\Services\DateService;

class DashboardController extends Controller
{
    public function __construct(
        protected DateService $dateService
    ) {
        // ...
    }

    public function home()
    {
        return View::make( 'ns::pages.dashboard.home', [
            'title' => __( 'Dashboard' ),
        ] );
    }
}
