<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            OrganizationsSeeder::class,
            AgentsSeeder::class,
            HelperSeeder::class,
            HelpSeekersSeeder::class,
            ProductCategoriesSeeder::class,
            ProductsSeeder::class,
            PeopleAidsSeeder::class,
            PackagesSeeder::class,
            PackageItemsSeeder::class,
            AidAllocationsSeeder::class,
            PackageAllocationsSeeder::class,
        ]);
    }
}
