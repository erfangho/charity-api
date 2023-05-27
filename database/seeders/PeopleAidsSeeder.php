<?php

namespace Database\Seeders;

use App\Models\PeopleAid;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PeopleAidsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PeopleAid::create([
            'title' => 'کاپشن',
            'product_id' => '1',
            'helper_id' => '1',
            'quantity' => 10,
        ]);
    }
}
