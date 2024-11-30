<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        for ($i = 0; $i < 500; $i++) {
            Product::create([
                'name' => 'Name ' . $i,
                'price' => $i,
                'description' => 'Description ' . $i,
                'image' => 'image' . $i,
                'store_id' => Store::all()->random()->id,
                'quantity' => rand(1, 10),
                'discount' => rand(1, 10),
            ]);
        }

    }
}
