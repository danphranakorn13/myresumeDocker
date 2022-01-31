<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Use Model
use App\Models\User;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'fname'=>'Mongkon',
            'lname'=>'Duangdao',
            'username'=>'DanPhranakorn',
            'email'=>'danphranakorn@gmail.com',
            'password'=>bcrypt('@13Dsawcxzs'),
            'tel'=>'092-385-6994',
            'avatar'=>'https://via.placeholder.com/400x400.png/004466?text=Dan+Pharanakorn',
            'roles'=> 1
        ]);

        User::factory(99)->create();
        Product::factory(5000)->create();
    }
}
