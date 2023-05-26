<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'erfanemune',
            'first_name' => 'Erfan',
            'last_name' => 'Ghorbani',
            'national_code' => '0023227052',
            'phone_number' => '09366223096',
            'address' => 'Tehran, Hashemi rafsanjani highway, Sardarjangal street',
            'role' => 'manager',
            'email' => 'erfan2ghorbani@gmail.com',
            'password' => Hash::make('13791379'),
        ]);
    }
}
