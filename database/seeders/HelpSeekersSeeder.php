<?php

namespace Database\Seeders;

use App\Models\HelpSeeker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HelpSeekersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HelpSeeker::create([
            'user_id' => '4',
            'Agent_id' => '1',
        ]);
    }
}
