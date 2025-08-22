<?php

namespace Ns\Console\Commands;

use Illuminate\Console\Command;
use Ns\Services\SetupService;

class InstallCommand extends Command
{
    protected $signature = 'ns:install {--force} {--routes} {--filesystem} {--views}';

    protected $description = 'Install NexoPOS required files and configurations.';

    public function handle()
    {
        $this->handleFileSystem();
        $this->handleApiRoutes();
    }

    private function handleFileSystem()
    {
        $setupService = app()->make( SetupService::class );

        if ( $this->option( 'filesystem' ) ) {
            $setupService->clearRegisteredFileSystem();

            $setupService->registerFileSystem(
                key: 'ns',
                function: 'base_path',
                path: ''
            );

            $setupService->registerFileSystem(
                key: 'ns-modules',
                function: 'base_path',
                path: 'modules'
            );

            $setupService->registerFileSystem(
                key: 'ns-modules-temp',
                function: 'storage_path',
                path: 'temporary-files/modules'
            );

            $setupService->registerFileSystem(
                key: 'ns-temp',
                function: 'storage_path',
                path: 'temporary-files'
            );

            $setupService->registerFileSystem(
                key: 'ns-public',
                function: 'base_path',
                path: 'public'
            );

            $setupService->registerFileSystem(
                key: 'snapshots',
                function: 'storage_path',
                path: 'snapshots'
            );

            $this->info( 'Filesystem registered' );
        }
    }

    private function handleApiRoutes()
    {
        if ( ! $this->option( 'routes' ) ) {
            return;
        }

        $apiFile = base_path( 'routes/api.php' );

        if ( ! file_exists( $apiFile ) ) {
            return $this->error( __( 'An api file was found. Make sure to create one and try again.' ) );
        }

        $apiContent = file_get_contents( $apiFile );

        if ( str_contains( $apiContent, 'Ns\\Events\\LoadApiRouteEvent' ) ) {
            $this->info( 'Api routes already registered' );

            return;
        }

        // now we'll add a new line at the end of the file and before the closing php tag
        $apiContent = str_replace( '?>', '', $apiContent );
        $apiContent .= "\n\n";
        $apiContent .= "// NexoPOS API routes\n";
        $apiContent .= "Ns\\Events\\LoadApiRouteEvent::dispatch();\n";
        $apiContent .= "\n\n";

        // now we'll add the new content to the file
        file_put_contents( $apiFile, $apiContent );

        $this->info( 'Registering NexoPOS API routes' );
    }
}
