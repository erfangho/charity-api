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
            'quantity' => '2',
            'help_seeker_id' => '1',
            'people_aid_id' => '1',
            'status' => 'assigned',
        ]);

        AidAllocation::create([
            'agent_id' => '1',
            'quantity' => '2',
            'help_seeker_id' => '1',
            'people_aid_id' => '1',
            'status' => 'not_assigned',
        ]);

        AidAllocation::create([
            'agent_id' => '1',
            'quantity' => '2',
            'help_seeker_id' => '1',
            'people_aid_id' => '1',
            'status' => 'assigned',
        ]);

        AidAllocation::create([
            'agent_id' => '1',
            'quantity' => '2',
            'help_seeker_id' => '1',
            'people_aid_id' => '1',
            'status' => 'assigned',
        ]);
    }
}
