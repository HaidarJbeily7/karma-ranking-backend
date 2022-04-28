<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Image;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Image::factory(3)->create();
        User::factory(100000)->create();
    }
}
