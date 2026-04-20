<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SettingSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            PostTopicSeeder::class,
            PageSeeder::class,
            ProductSeeder::class,
            PromotionSeeder::class,
            BannerSeeder::class,
            PostSeeder::class,
            ContactSeeder::class,
            StockReceiptSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}