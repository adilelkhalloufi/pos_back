<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Database\Seeder;

class MenuCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = Menu::query()->get()->keyBy('name');

        $categories = [
            'Menu Petit déjeuner' => [
                [
                    'name' => 'Entrées',
                    'description' => 'Petites bouchées pour commencer le repas.',
                    'display_order' => 1,
                ],
                [
                    'name' => 'Plats principaux',
                    'description' => 'Choix principaux pour un vrai petit déjeuner.',
                    'display_order' => 2,
                ],
                [
                    'name' => 'Pâtisseries',
                    'description' => 'Viennoiseries et douceurs matinaires.',
                    'display_order' => 3,
                ],
            ],
            'Menu Déjeuner' => [
                [
                    'name' => 'Entrées',
                    'description' => 'Entrées fraîches pour l repas du midi.',
                    'display_order' => 1,
                ],
                [
                    'name' => 'Plats principaux',
                    'description' => 'Plats du déjeuner bien équilibrés.',
                    'display_order' => 2,
                ],
                [
                    'name' => 'Desserts',
                    'description' => 'Desserts légers pour terminer en douceur.',
                    'display_order' => 3,
                ],
            ],
            'Menu Dîner' => [
                [
                    'name' => 'Entrées',
                    'description' => 'Délicieuses entrées pour commencer le dîner.',
                    'display_order' => 1,
                ],
                [
                    'name' => 'Plats principaux',
                    'description' => 'Plats du soir savoureux et gourmands.',
                    'display_order' => 2,
                ],
                [
                    'name' => 'Desserts',
                    'description' => 'Desserts sophistiqués pour finir en beauté.',
                    'display_order' => 3,
                ],
            ],
            'Menu Boissons' => [
                [
                    'name' => 'Cafés et thés',
                    'description' => 'Boissons chaudes pour accompagner votre pause.',
                    'display_order' => 1,
                ],
                [
                    'name' => 'Cocktails',
                    'description' => 'Cocktails frais et créations maison.',
                    'display_order' => 2,
                ],
                [
                    'name' => 'Boissons fraîches',
                    'description' => 'Jus, sodas et boissons froides.',
                    'display_order' => 3,
                ],
            ],
        ];

        foreach ($categories as $menuName => $menuCategories) {
            $menu = $menus->get($menuName);
            if (!$menu) {
                continue;
            }

            foreach ($menuCategories as $categoryData) {
                MenuCategory::updateOrCreate(
                    [
                        'menu_id' => $menu->id,
                        'name' => $categoryData['name'],
                    ],
                    array_merge($categoryData, [
                        'menu_id' => $menu->id,
                        'is_active' => true,
                    ])
                );
            }
        }
    }
}
