<?php

namespace Database\Seeders;

use App\Models\Manager;
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

        User::create([
            'username' => 'mrouhani',
            'first_name' => 'Mohammadreza',
            'last_name' => 'Rouhani',
            'national_code' => '0023227053',
            'phone_number' => '09366223097',
            'address' => 'Mazandaran, Tonekabon',
            'role' => 'agent',
            'email' => 'mrouhani@gmail.com',
            'password' => Hash::make('13791379'),
        ]);

        User::create([
            'username' => 'arghavanff',
            'first_name' => 'Arghavan',
            'last_name' => 'Falakfarsa',
            'national_code' => '0023227054',
            'phone_number' => '09366223098',
            'address' => 'West Azerbaijan, Urmia',
            'role' => 'helper',
            'email' => 'arghavanff@gmail.com',
            'password' => Hash::make('13791379'),
        ]);
    }
}
