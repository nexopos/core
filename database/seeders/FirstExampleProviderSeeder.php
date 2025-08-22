<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ns\Models\Provider;
use Ns\Models\User;

class FirstExampleProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $author = User::get()->map( fn( $user ) => $user->id )
            ->shuffle()
            ->first();

        $provider = new Provider;
        $provider->name = 'John Provider';
        $provider->email = 'john@nexopos.com';
        $provider->author = $author;
        $provider->save();

        $provider = new Provider;
        $provider->name = 'Mario Provider';
        $provider->email = 'mario@nexopos.com';
        $provider->author = $author;
        $provider->save();

        $provider = new Provider;
        $provider->name = 'Nate Provider';
        $provider->email = 'nate@nexopos.com';
        $provider->author = $author;
        $provider->save();
    }
}
