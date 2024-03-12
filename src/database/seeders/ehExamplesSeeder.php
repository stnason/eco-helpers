<?php

namespace ScottNason\EcoHelpers\database\seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


use ScottNason\EcoHelpers\Models\ehExample;

/**
 *  Not sure this has been implemented or in use anywhere. (?)
 */
class ehExamplesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        ehExample::factory(10)->create();

    }
}