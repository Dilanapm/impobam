<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            'Suavelina',
            'Ballerina Shampoo',
            'Ballerina Acondicionador',
            'Ballerina Jabón Líquido',
            'Toallas Húmedas Sister Mayumi 100 unidades',
            'Toallas Húmedas Sister Mayumi 50 unidades',
            'Ballerina Mixta',
        ];

        foreach ($products as $product) {
            Product::firstOrCreate([
                'name' => $product,
            ]);
        }
    }
}