<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ns\Models\Provider;
use Ns\Models\Role;

class DefaultProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return Provider::create( [
            'first_name' => __( 'Default Provider' ),
            'author' => Role::namespace( Role::ADMIN )->users->first()->id,
        ] );
    }
}
