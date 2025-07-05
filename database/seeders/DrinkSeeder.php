<?php

namespace Database\Seeders;

use App\Models\Bar;
use App\Models\Drink;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DrinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $krone = Bar::where('name', 'Krone')->first();

        Drink::create([
            'bar_id' => $krone->id,
            'name' => 'Mexikaner',
            'price' => 1.50,
        ]);
    }
}
