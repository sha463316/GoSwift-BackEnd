<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        for ($i = 0; $i < 10; $i++) {
            Store::create([
                'name' => 'Store ' . $i,
                'address' => 'Address ' . $i,
                'description' => 'Description ' . $i,
                'logo' => 'Logo ' . $i,

            ]);

        }


    }
}
