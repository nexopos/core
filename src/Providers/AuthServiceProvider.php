<?php

namespace Ns\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Ns\Models\PersonalAccessToken;
use Ns\Services\CoreService;

class AuthServiceProvider extends ServiceProvider
{
    public function boot( CoreService $coreService )
    {
        $coreService->registerGatePermissions();

        Sanctum::usePersonalAccessTokenModel( PersonalAccessToken::class );
    }

    public function register() {}
}
