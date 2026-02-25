<?php

use Illuminate\Support\Facades\Auth;
use Ns\Models\UserAddress;
use Ns\Services\UsersService;

/**
 * @var UsersService
 */
$usersService = app()->make( UsersService::class );

return [
    'label' => __( 'Biling' ),
    'identifier' => 'billing',
    'fields' => $usersService->getAddressFields( UserAddress::from( Auth::id(), 'billing' )->first() ),
];
