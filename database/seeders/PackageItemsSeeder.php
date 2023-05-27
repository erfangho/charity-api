<?php

namespace Database\Seeders;

use App\Models\PackageItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PackageItem::create([
            'product_id' => '2',
            'package_id' => '1',
            'quantity' => 6,
        ]);
    }
}
