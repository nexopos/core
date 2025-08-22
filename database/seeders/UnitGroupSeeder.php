<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ns\Models\UnitGroup;

class UnitGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return UnitGroup::factory()
            ->count( 5 )
            ->hasUnits( 8 )
            ->create();
    }
}
