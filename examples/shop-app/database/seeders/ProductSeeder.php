<?php

declare(strict_types=1);

namespace Database\Seeders;

use Newla\Core\Database\Seeder;
use Newla\Core\Database\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'name' => 'NEWLA Pro Laptop 16"',
                'slug' => 'newla-pro-laptop-16',
                'description' => 'Ultra high performance developer laptop powered by ARM architecture.',
                'price' => 1999.00,
                'stock' => 50,
                'image' => '/assets/images/laptop.png',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Mechanical Wireless Keyboard',
                'slug' => 'mechanical-wireless-keyboard',
                'description' => 'Custom mechanical switches with hot-swappable PCB and RGB backlighting.',
                'price' => 149.50,
                'stock' => 120,
                'image' => '/assets/images/keyboard.png',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Ergonomic Vertical Mouse',
                'slug' => 'ergonomic-vertical-mouse',
                'description' => 'Designed for all-day coding comfort and wrist strain relief.',
                'price' => 79.00,
                'stock' => 200,
                'image' => '/assets/images/mouse.png',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);
    }
}