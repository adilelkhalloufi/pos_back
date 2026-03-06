<?php

namespace Database\Seeders;

use App\Models\ModePayemnt;
use App\Models\PayementTermes;
use Illuminate\Database\Seeder;

class ModePayemntSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(database_path('import/mode_payement.json'));
        $data = json_decode($json, true);

        foreach ($data as $mode) {
            $regionModel = ModePayemnt::create([
                'name' => $mode['name'],
            ]);
        }

    
    }
}
