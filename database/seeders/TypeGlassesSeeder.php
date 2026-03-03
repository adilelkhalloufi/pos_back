<?php

namespace Database\Seeders;

use App\Models\TypeGlasses;
use Illuminate\Database\Seeder;

class TypeGlassesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $json = file_get_contents(database_path('import/typeglasse.json'));
        $data = json_decode($json, true);

        foreach ($data as $type) {
            $typeglasse = TypeGlasses::create([

                TypeGlasses::COL_NAME => $type['type'],
                TypeGlasses::COL_PRICE => $type['price'] ?? 0,

            ]);
        }
    }
}
