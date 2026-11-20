<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(EventMasterSeeder::class);

        if (app()->environment('local', 'testing')) {
            $this->call(DemoAccountsSeeder::class);
        }
    }
}
