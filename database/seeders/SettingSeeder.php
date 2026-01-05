<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Setting::updateOrCreate(['key' => 'security.rate_limit_global'], [
            'value' => '1000',
            'type' => 'number',
            'group' => 'security',
            'description' => 'Global API rate limit per minute'
        ]);

        \App\Models\Setting::updateOrCreate(['key' => 'security.rate_limit_login'], [
            'value' => '5',
            'type' => 'number',
            'group' => 'security',
            'description' => 'Login rate limit per minute'
        ]);
    }
}
