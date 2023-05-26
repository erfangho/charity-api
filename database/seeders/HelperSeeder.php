<?php

namespace Database\Seeders;

use App\Models\Helper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HelperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Helper::create([
            'user_id' => '3',
            'Agent_id' => '1',
        ]);
    }
}
