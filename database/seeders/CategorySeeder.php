<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Cambodia News',
            'World News',
            'Technology',
            'Business',
            'Lifestyle'
        ];

        foreach ($categories as $name){
            Category::firstOrCreate([
                'name' => $name,
                'slug' => Str::slug($name)
            ]);
        }
    }
}
