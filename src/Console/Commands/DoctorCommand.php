<?php

namespace Ns\Console\Commands;

use Ns\Services\DoctorService;
use Illuminate\Console\Command;

class DoctorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ns:doctor
        {--clear-modules-temp}
        {--fix-roles} 
        {--fix-users-attributes} 
        {--fix-domains}
        {--set-unit-visibility=}
        {--fix-duplicate-options}
        {--purge-orphan-migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will perform various tasks to fix issues on NexoPOS.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $doctorService = new DoctorService( $this );

        if ( $this->option( 'fix-roles' ) ) {
            $doctorService->restoreRoles();

            return $this->info( 'The roles where correctly restored.' );
        }

        if ( $this->option( 'fix-users-attributes' ) ) {
            $doctorService->createUserAttribute();

            return $this->info( 'The users attributes were fixed.' );
        }

        if ( $this->option( 'fix-duplicate-options' ) ) {
            $doctorService->fixDuplicateOptions();

            return $this->info( 'The duplicated options were cleared.' );
        }

        if ( $this->option( 'fix-domains' ) ) {
            $doctorService->fixDomains();

            return $this->info( 'The domain is correctly configured.' );
        }
        if ( $this->option( 'clear-modules-temp' ) ) {
            return $doctorService->clearTemporaryFiles();
        }

        if ( $this->option( 'purge-orphan-migrations' ) ) {
            return $doctorService->purgeRemovedMigrations();
        }
    }
}
