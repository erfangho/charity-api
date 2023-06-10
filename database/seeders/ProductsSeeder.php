<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'کاپشن',
            'category_id' => '1',
            'type' => 'product',
            'quantity' => 30,
        ]);

        Product::create([
            'name' => 'خودکار',
            'category_id' => '2',
            'type' => 'product',
            'quantity' => 30,
        ]);

        Product::create([
            'name' => 'نقدی',
            'type' => 'cash',
            'quantity' => 30,
        ]);
    }
}
