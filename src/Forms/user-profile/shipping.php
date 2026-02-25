<?php

use Illuminate\Support\Facades\Auth;
use Ns\Models\UserAddress;
use Ns\Services\UsersService;

/**
 * @var UsersService
 */
$usersService = app()->make( UsersService::class );

return [
    'label' => __( 'Shipping' ),
    'identifier' => 'shipping',
    'fields' => $usersService->getAddressFields( UserAddress::from( Auth::id(), 'shipping' )->first() ),
];
