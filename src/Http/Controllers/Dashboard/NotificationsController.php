<?php

/**
 * NexoPOS Controller
 *
 * @since  1.0
 **/

namespace Ns\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\Auth;
use Ns\Http\Controllers\DashboardController;
use Ns\Models\Notification;
use Ns\Services\DateService;
use Ns\Services\NotificationService;

class NotificationsController extends DashboardController
{
    public function __construct(
        protected NotificationService $notificationService,
        protected DateService $dateService
    ) {
        // ...
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        return Notification::for( Auth::id() )->orderBy( 'id', 'desc' )->get();
    }

    /**
     * @return array
     */
    public function deleteSingleNotification( $id )
    {
        $this->notificationService->deleteSingleNotification( $id );

        return [
            'status' => 'success',
            'message' => __( 'The notification has been successfully deleted' ),
        ];
    }

    /**
     * @return array
     */
    public function deletAllNotifications()
    {
        $this->notificationService->deleteNotificationsFor( Auth::user() );

        return [
            'status' => 'success',
            'message' => __( 'All the notifications have been cleared.' ),
        ];
    }
}
