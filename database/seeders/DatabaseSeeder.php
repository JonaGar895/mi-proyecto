<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AccountSeeder::class,
            CategorySeeder::class,
            PaintingSeeder::class,
            ExhibitionSeeder::class,
            PublicationSeeder::class,
            FavoriteSeeder::class,
            CommentSeeder::class,
            BalanceTransactionSeeder::class,
            SaleSeeder::class,
            InquirySeeder::class,
        ]);
    }
}
