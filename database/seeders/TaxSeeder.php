<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ns\Models\TaxGroup;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return TaxGroup::factory()
            ->count( 5 )
            ->hasTaxes( 2 )
            ->create();
    }
}
