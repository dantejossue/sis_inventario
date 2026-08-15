<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // El orden importa: roles antes que usuarios
        $this->call([
            RolSeeder::class,
        ]);
    }
}
