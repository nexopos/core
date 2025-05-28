<?php
namespace Ns\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RefreshAssetsCommand extends Command
{
    public $signature = 'ns:publish-assets';

    public $description = 'Publish/Refresh NexoPOS Core assets.';

    public function handle()
    {
        /**
         * we'll locate the directory "public/vendor/ns" and delete it.
         */
        $vendorPath = public_path('vendor/ns');

        if (is_dir($vendorPath)) {
            $this->info('Removing existing assets...');
            
            Storage::deleteDirectory( $vendorPath );
            
            $this->info('Assets removed successfully.');
        }

        $this->info('Publishing assets...');

        $this->call( 'vendor:publish', [
            '--tag' => 'nexopos-assets',
            '--force' => true,
        ]);

        $this->info('Assets published successfully.');
    }
}