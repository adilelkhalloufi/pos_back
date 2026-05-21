<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Store;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::query()->first();
        if (!$store) {
            return;
        }

        $menus = [
            [
                'name' => 'Menu Petit déjeuner',
                'description' => 'Sélection de petits déjeuners pour bien commencer la journée.',
                'type' => Menu::TYPE_BREAKFAST,
                'available_from_time' => '07:00:00',
                'available_to_time' => '10:30:00',
                'display_order' => 1,
            ],
            [
                'name' => 'Menu Déjeuner',
                'description' => 'Plats savoureux pour le déjeuner en milieu de journée.',
                'type' => Menu::TYPE_LUNCH,
                'available_from_time' => '11:00:00',
                'available_to_time' => '15:00:00',
                'display_order' => 2,
            ],
            [
                'name' => 'Menu Dîner',
                'description' => 'Repas du soir élégants et réconfortants.',
                'type' => Menu::TYPE_DINNER,
                'available_from_time' => '18:00:00',
                'available_to_time' => '22:30:00',
                'display_order' => 3,
            ],
            [
                'name' => 'Menu Boissons',
                'description' => 'Boissons chaudes et rafraîchissantes pour tous les moments.',
                'type' => Menu::TYPE_DRINKS,
                'available_from_time' => '10:00:00',
                'available_to_time' => '22:30:00',
                'display_order' => 4,
            ],
        ];

        foreach ($menus as $menuData) {
            Menu::updateOrCreate(
                [
                    'name' => $menuData['name'],
                    'store_id' => $store->id,
                ],
                array_merge($menuData, [
                    'store_id' => $store->id,
                    'is_active' => true,
                ])
            );
        }
    }
}
