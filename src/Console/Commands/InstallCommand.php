<?php

namespace Ns\Console\Commands;

use Illuminate\Console\Command;
use Ns\Services\SetupService;

class InstallCommand extends Command
{
    protected $signature = 'ns:install {--force} {--routes} {--filesystem} {--views} {--scheduling}';

    protected $description = 'Install NexoPOS required files and configurations.';

    public function handle()
    {
        $this->handleFileSystem();
        $this->handleApiRoutes();
        $this->handleScheduling();
    }

    private function handleScheduling()
    {
        if ( ! $this->option( 'scheduling' ) ) {
            return;
        }

        $appFile = base_path( 'bootstrap/app.php' );

        if ( ! file_exists( $appFile ) ) {
            return $this->error( __( 'The bootstrap/app.php file was not found. Make sure to create one and try again.' ) );
        }

        $appContent = file_get_contents( $appFile );

        // Check if scheduling is already configured
        if ( str_contains( $appContent, '->withSchedule(' ) ) {
            // Check if ModuleRouting::schedule is already present
            if ( str_contains( $appContent, 'ModuleRouting::schedule(' ) ) {
                $this->info( 'Scheduling support already configured' );
                return;
            }

            // withSchedule exists but ModuleRouting::schedule is not called
            // We need to add it to the callback
            $this->addScheduleCallToExistingWithSchedule( $appFile, $appContent );
        } else {
            // withSchedule does not exist, we need to add it
            $this->addWithScheduleMethod( $appFile, $appContent );
        }

        $this->info( 'Scheduling support registered' );
    }

    private function addScheduleCallToExistingWithSchedule( $appFile, $appContent )
    {
        // Ensure Schedule is imported
        $appContent = $this->ensureScheduleImport( $appContent );

        // Ensure ModuleRouting is imported
        $appContent = $this->ensureModuleRoutingImport( $appContent );

        // Find the withSchedule callback and add ModuleRouting::schedule call
        // Pattern: ->withSchedule( function( Schedule $schedule) {
        if ( preg_match( '/->withSchedule\(\s*function\s*\(\s*(?:Schedule\s+)?\$schedule\s*\)\s*{/', $appContent, $matches, PREG_OFFSET_CAPTURE ) ) {
            $insertPosition = $matches[0][1] + strlen( $matches[0][0] );
            
            // Check if there's already content in the callback
            $beforeInsert = substr( $appContent, 0, $insertPosition );
            $afterInsert = substr( $appContent, $insertPosition );
            
            // Add ModuleRouting::schedule call with proper indentation
            $scheduleCall = "\n        // NexoPOS Core: Scheduling Start\n        ModuleRouting::schedule( \$schedule );\n        // NexoPOS Core: Scheduling End";
            
            $appContent = $beforeInsert . $scheduleCall . $afterInsert;
            
            file_put_contents( $appFile, $appContent );
        } else {
            // Try alternative pattern without type hint
            if ( preg_match( '/->withSchedule\(\s*function\s*\(\s*\$schedule\s*\)\s*{/', $appContent, $matches, PREG_OFFSET_CAPTURE ) ) {
                // Update the function signature to include Schedule type hint
                $oldSignature = $matches[0][0];
                $newSignature = str_replace( '($schedule)', '(Schedule $schedule)', $oldSignature );
                
                $appContent = str_replace( $oldSignature, $newSignature, $appContent );
                
                // Now add the ModuleRouting::schedule call
                $insertPosition = strpos( $appContent, $newSignature ) + strlen( $newSignature );
                $beforeInsert = substr( $appContent, 0, $insertPosition );
                $afterInsert = substr( $appContent, $insertPosition );

                $scheduleCall = "\n        // NexoPOS Core: Scheduling Start\n        ModuleRouting::schedule( \$schedule );\n        // NexoPOS Core: Scheduling End";

                $appContent = $beforeInsert . $scheduleCall . $afterInsert;
                
                file_put_contents( $appFile, $appContent );
            }
        }
    }

    private function addWithScheduleMethod( $appFile, $appContent )
    {
        // Ensure Schedule is imported
        $appContent = $this->ensureScheduleImport( $appContent );

        // Ensure ModuleRouting is imported
        $appContent = $this->ensureModuleRoutingImport( $appContent );

        // Find the ->withRouting() method and add ->withSchedule() after it
        // We need to properly find the closing parenthesis by counting brackets
        if ( preg_match( '/->withRouting\(/', $appContent, $matches, PREG_OFFSET_CAPTURE ) ) {
            $startPosition = $matches[0][1] + strlen( $matches[0][0] ); // Position after "->withRouting("
            
            // Count parentheses to find the matching closing parenthesis
            $parenCount = 1;
            $position = $startPosition;
            $length = strlen( $appContent );
            
            while ( $position < $length && $parenCount > 0 ) {
                $char = $appContent[$position];
                
                if ( $char === '(' ) {
                    $parenCount++;
                } elseif ( $char === ')' ) {
                    $parenCount--;
                }
                
                $position++;
            }
            
            // $position is now right after the closing parenthesis of withRouting
            if ( $parenCount === 0 ) {
                $beforeInsert = substr( $appContent, 0, $position );
                $afterInsert = substr( $appContent, $position );
                
                $withSchedule = "\n    ->withSchedule( function( Schedule \$schedule ) {\n        // NexoPOS: Module scheduling support - remove this line if uninstalling NexoPOS\n        ModuleRouting::schedule( \$schedule );\n        // End NexoPOS\n    })";
                
                $appContent = $beforeInsert . $withSchedule . $afterInsert;
                
                file_put_contents( $appFile, $appContent );
            }
        }
    }

    private function ensureScheduleImport( $appContent )
    {
        if ( ! str_contains( $appContent, 'use Illuminate\Console\Scheduling\Schedule;' ) ) {
            // Find the first use statement and add Schedule import after it
            if ( preg_match( '/^<\?php\s*\n/m', $appContent, $matches, PREG_OFFSET_CAPTURE ) ) {
                $insertPosition = $matches[0][1] + strlen( $matches[0][0] );
                
                $beforeInsert = substr( $appContent, 0, $insertPosition );
                $afterInsert = substr( $appContent, $insertPosition );
                
                $import = "\nuse Illuminate\\Console\\Scheduling\\Schedule;";
                
                $appContent = $beforeInsert . $import . $afterInsert;
            }
        }

        return $appContent;
    }

    private function ensureModuleRoutingImport( $appContent )
    {
        if ( ! str_contains( $appContent, 'use Ns\Classes\ModuleRouting;' ) ) {
            // Find the last use statement and add ModuleRouting import after it
            if ( preg_match_all( '/^use .+;$/m', $appContent, $matches, PREG_OFFSET_CAPTURE ) ) {
                $lastUse = end( $matches[0] );
                $insertPosition = $lastUse[1] + strlen( $lastUse[0] );
                
                $beforeInsert = substr( $appContent, 0, $insertPosition );
                $afterInsert = substr( $appContent, $insertPosition );
                
                $import = "\nuse Ns\\Classes\\ModuleRouting;";
                
                $appContent = $beforeInsert . $import . $afterInsert;
            }
        }

        return $appContent;
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
