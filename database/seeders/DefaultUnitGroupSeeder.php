<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ns\Models\Role;
use Ns\Models\Unit;
use Ns\Models\UnitGroup;

class DefaultUnitGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $unitGroup = UnitGroup::create( [
            'name' => __( 'Countable' ),
            'author' => Role::namespace( 'admin' )->users->first()->id,
        ] );

        $piece = Unit::create( [
            'name' => __( 'Piece' ),
            'value' => 1,
            'identifier' => 'piece',
            'base_unit' => true,
            'group_id' => $unitGroup->id,
            'author' => Role::namespace( 'admin' )->users->first()->id,
        ] );
    }
}
