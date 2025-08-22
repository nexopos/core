<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ns\Models\CustomerGroup;

class CustomerGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return CustomerGroup::factory()
            ->count( 10 )
            ->hasCustomers( 10 )
            ->create();
    }
}
