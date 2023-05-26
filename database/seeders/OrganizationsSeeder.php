<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrganizationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Organization::create([
            'name' => 'Emam Ali charity',
            'phone_number' => '02144858687',
            'description' => 'سازمان مرکزی خیریه در تهران',
            'address' => 'Tehran, Emam ali boulevard',
        ]);
    }
}
