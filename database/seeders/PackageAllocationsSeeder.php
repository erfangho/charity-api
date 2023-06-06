<?php

namespace Database\Seeders;

use App\Models\PackageAllocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageAllocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PackageAllocation::create([
            'agent_id' => '1',
            'quantity' => '2',
            'help_seeker_id' => '1',
            'package_id' => '1',
            'status' => 'unallocated',
        ]);
    }
}
