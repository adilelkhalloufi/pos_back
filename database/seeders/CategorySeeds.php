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
            ['name' => 'CHOCOLATS'],
            ['name' => 'PARFUMS'],
            ['name' => 'ACCESSOIRES'],
            ['name' => 'MONTRES'],
            ['name' => 'LUNETTES'],
            ['name' => 'JOUETS'],
            ['name' => 'BIJOUX'],
            ['name' => 'SNACKS'],
            ['name' => 'TABACS  A  ROULER'],
            ['name' => 'CIGARILLOS'],
            ['name' => 'COSMETIQUES'],
            ['name' => 'CIGARETTES'],
            ['name' => 'DEODORANTS'],
            ['name' => 'DEMAQUILLANTS PREPARATION LAV PEAU '],
            ['name' => 'SUCRERIES'],
            ['name' => 'PRINGELS'],
            ['name' => 'FRUITS SECS'],
            ['name' => 'CHEWING GUM'],
            ['name' => 'BISCUITS'],
            ['name' => 'testers'],
            ['name' => 'stylos'],
            ['name' => 'cigares'],
            ['name' => 'crayons destines'],
            ['name' => 'outil de pedicures'],
            ['name' => 'BONBONS'],
            ['name' => 'ARACHIDES'],
            ['name' => 'sac'],
            ['name' => 'ASSORTIMENTS DE VOYAGE'],
            ['name' => 'COULEURS DESTINES'],
            ['name' => 'pinceaux destines'],
            ['name' => 'oreillers de voyage'],
            ['name' => 'couleurs '],
            ['name' => 'meubles presentoires '],
            ['name' => 'ecouteurs ']
            
        ];

        // Insert categories into the database
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
