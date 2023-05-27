<?php

namespace Database\Seeders;

use App\Models\AidAllocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AidAllocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AidAllocation::create([
            'agent_id' => '1',
            'help_seeker_id' => '1',
            'people_aid_id' => '1',
            'status' => 'unallocated',
        ]);
    }
}
