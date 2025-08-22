<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ns\Models\RewardSystem;

class RewardSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return RewardSystem::factory()
            ->count( 20 )
            ->hasRules( 4 )
            ->hasCoupon( 1 )
            ->create();
    }
}
