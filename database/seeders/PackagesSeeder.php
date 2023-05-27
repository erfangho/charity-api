<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Package::create([
            'title' => 'بسته حمایتی مخصوص ماه مبارک رمضان',
            'organization_id' => '1',
            'quantity' => 5,
        ]);
    }
}
