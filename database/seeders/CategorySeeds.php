<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeds extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         
 

        $categories = [
      
            
        ];

        // Insert categories into the database
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
