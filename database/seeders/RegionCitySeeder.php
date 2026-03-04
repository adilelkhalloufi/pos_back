<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // i want to create regin and city but i have file json with data in database/import/region_city.json

        // $json = file_get_contents(database_path('import/region.json'));
        // $data = json_decode($json, true);

        // foreach ($data as $region) {
        //     $regionModel = Region::create([
        //         'id' => $region['id'],
        //         'name' => $region['region'],
        //     ]);
        // }

        // $json = file_get_contents(database_path('import/city.json'));
        // $data = json_decode($json, true);

        // foreach ($data as $region) {
        //     $regionModel = City::create([
        //         'id' => $region['id'],
        //         'name' => $region['ville'],
        //         'region_id' => $region['region'],
        //     ]);

        // }
    }
}
