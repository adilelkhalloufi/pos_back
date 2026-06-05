<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnitsAndConversionsSeeder extends Seeder
{
    /**
     * Exécuter le seeding de la base de données.
     * 
     * Crée toutes les unités et conversions standard pour la gestion des stocks de restaurant.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // ========================================
        // ÉTAPE 1: CRÉER LES UNITÉS
        // ========================================

        $units = [
            // UNITÉS DE POIDS
            ['id' => 1, 'name' => 'Kilogramme', 'symbol' => 'kg', 'description' => 'Unité de poids standard - 1000 grammes', 'is_active' => true],
            ['id' => 2, 'name' => 'Gramme', 'symbol' => 'g', 'description' => 'Petite unité de poids', 'is_active' => true],
            ['id' => 3, 'name' => 'Milligramme', 'symbol' => 'mg', 'description' => 'Unité de poids minuscule', 'is_active' => true],
            ['id' => 4, 'name' => 'Livre', 'symbol' => 'lb', 'description' => 'Unité de poids impériale - 453,592 grammes', 'is_active' => true],
            ['id' => 5, 'name' => 'Once', 'symbol' => 'oz', 'description' => 'Unité de poids impériale - 28,35 grammes', 'is_active' => true],
            ['id' => 6, 'name' => 'Tonne', 'symbol' => 't', 'description' => 'Grande unité de poids - 1000 kilogrammes', 'is_active' => true],

            // UNITÉS DE VOLUME
            ['id' => 7, 'name' => 'Litre', 'symbol' => 'L', 'description' => 'Unité de volume standard - 1000 millilitres', 'is_active' => true],
            ['id' => 8, 'name' => 'Millilitre', 'symbol' => 'ml', 'description' => 'Petite unité de volume', 'is_active' => true],
            ['id' => 9, 'name' => 'Gallon', 'symbol' => 'gal', 'description' => 'Gallon américain - 3,785 litres', 'is_active' => true],
            ['id' => 10, 'name' => 'Quart', 'symbol' => 'qt', 'description' => 'Quart américain - 0,946 litres', 'is_active' => true],
            ['id' => 11, 'name' => 'Pinte', 'symbol' => 'pt', 'description' => 'Pinte américaine - 473 millilitres', 'is_active' => true],
            ['id' => 12, 'name' => 'Tasse', 'symbol' => 'tasse', 'description' => 'Tasse américaine - 237 millilitres', 'is_active' => true],
            ['id' => 13, 'name' => 'Cuillère à soupe', 'symbol' => 'c. à s.', 'description' => 'Cuillère à soupe - 15 millilitres', 'is_active' => true],
            ['id' => 14, 'name' => 'Cuillère à café', 'symbol' => 'c. à c.', 'description' => 'Cuillère à café - 5 millilitres', 'is_active' => true],

            // UNITÉS DE COMPTAGE
            ['id' => 15, 'name' => 'Pièce', 'symbol' => 'pc', 'description' => 'Unité individuelle', 'is_active' => true],
            ['id' => 16, 'name' => 'Douzaine', 'symbol' => 'dz', 'description' => '12 pièces', 'is_active' => true],
            ['id' => 17, 'name' => 'Boîte', 'symbol' => 'boîte', 'description' => 'Conteneur', 'is_active' => true],
            ['id' => 18, 'name' => 'Caisse', 'symbol' => 'caisse', 'description' => 'Grand conteneur', 'is_active' => true],
            ['id' => 19, 'name' => 'Cageot', 'symbol' => 'cageot', 'description' => 'Conteneur de stockage', 'is_active' => true],
            ['id' => 20, 'name' => 'Sac', 'symbol' => 'sac', 'description' => 'Emballage', 'is_active' => true],
            ['id' => 21, 'name' => 'Bouteille', 'symbol' => 'btl', 'description' => 'Bouteille', 'is_active' => true],
            ['id' => 22, 'name' => 'Canette', 'symbol' => 'canette', 'description' => 'Canette', 'is_active' => true],
            ['id' => 23, 'name' => 'Bocal', 'symbol' => 'bocal', 'description' => 'Bocal', 'is_active' => true],
            ['id' => 24, 'name' => 'Paquet', 'symbol' => 'paquet', 'description' => 'Paquet', 'is_active' => true],

            // UNITÉS DE SERVICE
            ['id' => 25, 'name' => 'Portion', 'symbol' => 'portion', 'description' => 'Portion individuelle', 'is_active' => true],
            ['id' => 26, 'name' => 'Service', 'symbol' => 'service', 'description' => 'Unité de service de recette', 'is_active' => true],
            ['id' => 27, 'name' => 'Assiette', 'symbol' => 'assiette', 'description' => 'Service en assiette', 'is_active' => true],
            ['id' => 28, 'name' => 'Verre', 'symbol' => 'verre', 'description' => 'Verre pour boisson', 'is_active' => true],
        ];

        // Insérer les unités avec timestamps
        foreach ($units as &$unit) {
            $unit['store_id'] = null;
            $unit['created_at'] = $now;
            $unit['updated_at'] = $now;
        }

        DB::table('units')->insert($units);

        $this->command->info('✅ ' . count($units) . ' unités créées');

        // ========================================
        // ÉTAPE 2: CRÉER LES CONVERSIONS D'UNITÉS
        // ========================================

        $conversions = [];

        // -------------------------------------
        // CONVERSIONS DE POIDS
        // -------------------------------------

        // Conversions Kilogramme
        $conversions[] = ['from_unit_id' => 1, 'to_unit_id' => 2, 'conversion_factor' => 1000, 'description' => '1 kg = 1000 g'];
        $conversions[] = ['from_unit_id' => 1, 'to_unit_id' => 4, 'conversion_factor' => 2.20462, 'description' => '1 kg = 2.20462 lb'];
        $conversions[] = ['from_unit_id' => 1, 'to_unit_id' => 5, 'conversion_factor' => 35.274, 'description' => '1 kg = 35.274 oz'];

        // Conversions Gramme
        $conversions[] = ['from_unit_id' => 2, 'to_unit_id' => 1, 'conversion_factor' => 0.001, 'description' => '1 g = 0.001 kg'];
        $conversions[] = ['from_unit_id' => 2, 'to_unit_id' => 3, 'conversion_factor' => 1000, 'description' => '1 g = 1000 mg'];
        $conversions[] = ['from_unit_id' => 2, 'to_unit_id' => 4, 'conversion_factor' => 0.00220462, 'description' => '1 g = 0.00220462 lb'];
        $conversions[] = ['from_unit_id' => 2, 'to_unit_id' => 5, 'conversion_factor' => 0.035274, 'description' => '1 g = 0.035274 oz'];

        // Conversions Milligramme (uniquement vers gramme, pas kg)
        $conversions[] = ['from_unit_id' => 3, 'to_unit_id' => 2, 'conversion_factor' => 0.001, 'description' => '1 mg = 0.001 g'];

        // Conversions Livre
        $conversions[] = ['from_unit_id' => 4, 'to_unit_id' => 1, 'conversion_factor' => 0.453592, 'description' => '1 lb = 0.453592 kg'];
        $conversions[] = ['from_unit_id' => 4, 'to_unit_id' => 2, 'conversion_factor' => 453.592, 'description' => '1 lb = 453.592 g'];
        $conversions[] = ['from_unit_id' => 4, 'to_unit_id' => 5, 'conversion_factor' => 16, 'description' => '1 lb = 16 oz'];

        // Conversions Once
        $conversions[] = ['from_unit_id' => 5, 'to_unit_id' => 1, 'conversion_factor' => 0.0283495, 'description' => '1 oz = 0.0283495 kg'];
        $conversions[] = ['from_unit_id' => 5, 'to_unit_id' => 2, 'conversion_factor' => 28.3495, 'description' => '1 oz = 28.3495 g'];
        $conversions[] = ['from_unit_id' => 5, 'to_unit_id' => 4, 'conversion_factor' => 0.0625, 'description' => '1 oz = 0.0625 lb'];

        // Conversions Tonne (uniquement vers kg, pas grammes)
        $conversions[] = ['from_unit_id' => 6, 'to_unit_id' => 1, 'conversion_factor' => 1000, 'description' => '1 t = 1000 kg'];
        $conversions[] = ['from_unit_id' => 1, 'to_unit_id' => 6, 'conversion_factor' => 0.001, 'description' => '1 kg = 0.001 t'];

        // -------------------------------------
        // CONVERSIONS DE VOLUME
        // -------------------------------------

        // Conversions Litre
        $conversions[] = ['from_unit_id' => 7, 'to_unit_id' => 8, 'conversion_factor' => 1000, 'description' => '1 L = 1000 ml'];
        $conversions[] = ['from_unit_id' => 7, 'to_unit_id' => 9, 'conversion_factor' => 0.264172, 'description' => '1 L = 0.264172 gal'];
        $conversions[] = ['from_unit_id' => 7, 'to_unit_id' => 10, 'conversion_factor' => 1.05669, 'description' => '1 L = 1.05669 qt'];
        $conversions[] = ['from_unit_id' => 7, 'to_unit_id' => 11, 'conversion_factor' => 2.11338, 'description' => '1 L = 2.11338 pt'];
        $conversions[] = ['from_unit_id' => 7, 'to_unit_id' => 12, 'conversion_factor' => 4.22675, 'description' => '1 L = 4.22675 cups'];

        // Conversions Millilitre
        $conversions[] = ['from_unit_id' => 8, 'to_unit_id' => 7, 'conversion_factor' => 0.001, 'description' => '1 ml = 0.001 L'];
        $conversions[] = ['from_unit_id' => 8, 'to_unit_id' => 13, 'conversion_factor' => 0.067628, 'description' => '1 ml = 0.067628 tbsp'];
        $conversions[] = ['from_unit_id' => 8, 'to_unit_id' => 14, 'conversion_factor' => 0.202884, 'description' => '1 ml = 0.202884 tsp'];

        // Conversions Gallon
        $conversions[] = ['from_unit_id' => 9, 'to_unit_id' => 7, 'conversion_factor' => 3.78541, 'description' => '1 gal = 3.78541 L'];
        $conversions[] = ['from_unit_id' => 9, 'to_unit_id' => 8, 'conversion_factor' => 3785.41, 'description' => '1 gal = 3785.41 ml'];
        $conversions[] = ['from_unit_id' => 9, 'to_unit_id' => 10, 'conversion_factor' => 4, 'description' => '1 gal = 4 qt'];

        // Conversions Quart
        $conversions[] = ['from_unit_id' => 10, 'to_unit_id' => 7, 'conversion_factor' => 0.946353, 'description' => '1 qt = 0.946353 L'];
        $conversions[] = ['from_unit_id' => 10, 'to_unit_id' => 8, 'conversion_factor' => 946.353, 'description' => '1 qt = 946.353 ml'];
        $conversions[] = ['from_unit_id' => 10, 'to_unit_id' => 9, 'conversion_factor' => 0.25, 'description' => '1 qt = 0.25 gal'];
        $conversions[] = ['from_unit_id' => 10, 'to_unit_id' => 11, 'conversion_factor' => 2, 'description' => '1 qt = 2 pt'];

        // Conversions Pinte
        $conversions[] = ['from_unit_id' => 11, 'to_unit_id' => 7, 'conversion_factor' => 0.473176, 'description' => '1 pt = 0.473176 L'];
        $conversions[] = ['from_unit_id' => 11, 'to_unit_id' => 8, 'conversion_factor' => 473.176, 'description' => '1 pt = 473.176 ml'];
        $conversions[] = ['from_unit_id' => 11, 'to_unit_id' => 10, 'conversion_factor' => 0.5, 'description' => '1 pt = 0.5 qt'];
        $conversions[] = ['from_unit_id' => 11, 'to_unit_id' => 12, 'conversion_factor' => 2, 'description' => '1 pt = 2 cups'];

        // Conversions Tasse
        $conversions[] = ['from_unit_id' => 12, 'to_unit_id' => 7, 'conversion_factor' => 0.236588, 'description' => '1 cup = 0.236588 L'];
        $conversions[] = ['from_unit_id' => 12, 'to_unit_id' => 8, 'conversion_factor' => 236.588, 'description' => '1 cup = 236.588 ml'];
        $conversions[] = ['from_unit_id' => 12, 'to_unit_id' => 13, 'conversion_factor' => 16, 'description' => '1 cup = 16 tbsp'];
        $conversions[] = ['from_unit_id' => 12, 'to_unit_id' => 14, 'conversion_factor' => 48, 'description' => '1 cup = 48 tsp'];

        // Conversions Cuillère à soupe
        $conversions[] = ['from_unit_id' => 13, 'to_unit_id' => 8, 'conversion_factor' => 14.7868, 'description' => '1 tbsp = 14.7868 ml'];
        $conversions[] = ['from_unit_id' => 13, 'to_unit_id' => 12, 'conversion_factor' => 0.0625, 'description' => '1 tbsp = 0.0625 cup'];
        $conversions[] = ['from_unit_id' => 13, 'to_unit_id' => 14, 'conversion_factor' => 3, 'description' => '1 tbsp = 3 tsp'];

        // Conversions Cuillère à café
        $conversions[] = ['from_unit_id' => 14, 'to_unit_id' => 8, 'conversion_factor' => 4.92892, 'description' => '1 tsp = 4.92892 ml'];
        $conversions[] = ['from_unit_id' => 14, 'to_unit_id' => 13, 'conversion_factor' => 0.333333, 'description' => '1 tsp = 0.333333 tbsp'];

        // -------------------------------------
        // CONVERSIONS DE COMPTAGE (Courantes)
        // -------------------------------------

        // Conversions Douzaine
        $conversions[] = ['from_unit_id' => 16, 'to_unit_id' => 15, 'conversion_factor' => 12, 'description' => '1 dozen = 12 pieces'];
        $conversions[] = ['from_unit_id' => 15, 'to_unit_id' => 16, 'conversion_factor' => 0.0833333, 'description' => '1 piece = 0.0833333 dozen'];

        // Conversions Boîte (exemple: boîte d'œufs standard)
        $conversions[] = ['from_unit_id' => 17, 'to_unit_id' => 15, 'conversion_factor' => 12, 'description' => '1 box = 12 pieces (default)'];

        // Conversions Caisse (exemple: caisse de boissons)
        $conversions[] = ['from_unit_id' => 18, 'to_unit_id' => 15, 'conversion_factor' => 24, 'description' => '1 case = 24 pieces (default)'];
        $conversions[] = ['from_unit_id' => 18, 'to_unit_id' => 21, 'conversion_factor' => 24, 'description' => '1 case = 24 bottles'];

        // Conversions Cageot
        $conversions[] = ['from_unit_id' => 19, 'to_unit_id' => 21, 'conversion_factor' => 24, 'description' => '1 crate = 24 bottles (default)'];

        // -------------------------------------
        // CONVERSIONS COURANTES RESTAURANT
        // -------------------------------------

        // Bouteille standard (vin/eau) = 750ml
        $conversions[] = ['from_unit_id' => 21, 'to_unit_id' => 8, 'conversion_factor' => 750, 'description' => '1 bottle = 750 ml (standard)'];
        $conversions[] = ['from_unit_id' => 21, 'to_unit_id' => 7, 'conversion_factor' => 0.75, 'description' => '1 bottle = 0.75 L'];

        // Canette standard (soda) = 330ml
        $conversions[] = ['from_unit_id' => 22, 'to_unit_id' => 8, 'conversion_factor' => 330, 'description' => '1 can = 330 ml (standard)'];

        // Verre pour bar: 1 bouteille = 5 verres
        $conversions[] = ['from_unit_id' => 21, 'to_unit_id' => 28, 'conversion_factor' => 5, 'description' => '1 bouteille = 5 verres'];
        $conversions[] = ['from_unit_id' => 28, 'to_unit_id' => 21, 'conversion_factor' => 0.2, 'description' => '1 verre = 0.2 bouteille'];
        $conversions[] = ['from_unit_id' => 28, 'to_unit_id' => 8, 'conversion_factor' => 150, 'description' => '1 verre = 150 ml'];
        $conversions[] = ['from_unit_id' => 8, 'to_unit_id' => 28, 'conversion_factor' => 0.00666667, 'description' => '1 ml = 0.00666667 verre'];

        // Ajouter timestamps et store_id à toutes les conversions
        foreach ($conversions as &$conversion) {
            $conversion['store_id'] = null; // Conversions globales
            $conversion['created_at'] = $now;
            $conversion['updated_at'] = $now;
            unset($conversion['description']); // Supprimer le champ description car il n'est pas dans la table
        }

        // Insérer les conversions par morceaux pour éviter les problèmes de mémoire
        $chunks = array_chunk($conversions, 50);
        foreach ($chunks as $chunk) {
            DB::table('unit_conversions')->insert($chunk);
        }

        $this->command->info('✅ ' . count($conversions) . ' conversions créées');

        // ========================================
        // RÉSUMÉ
        // ========================================

        $this->command->line('');
        $this->command->line('════════════════════════════════════════════════');
        $this->command->info('✅ SEEDER UNITÉS & CONVERSIONS TERMINÉ');
        $this->command->line('════════════════════════════════════════════════');
        $this->command->line('');
        $this->command->line('📊 Résumé:');
        $this->command->line('   • Unités de poids: 6 (kg, g, mg, lb, oz, tonne)');
        $this->command->line('   • Unités de volume: 8 (L, ml, gal, qt, pinte, tasse, c.à.s, c.à.c)');
        $this->command->line('   • Unités de comptage: 10 (pièce, douzaine, boîte, caisse, etc.)');
        $this->command->line('   • Unités de service: 3 (portion, service, assiette)');
        $this->command->line('   • Total Unités: ' . count($units));
        $this->command->line('   • Total Conversions: ' . count($conversions));
        $this->command->line('');
        $this->command->line('🎯 Conversions courantes disponibles:');
        $this->command->line('   • 1 kg = 1000 g');
        $this->command->line('   • 1 L = 1000 ml');
        $this->command->line('   • 1 lb = 453,592 g');
        $this->command->line('   • 1 gal = 3,78541 L');
        $this->command->line('   • 1 bouteille = 750 ml');
        $this->command->line('   • 1 douzaine = 12 pièces');
        $this->command->line('');
        $this->command->info('✨ Votre système est prêt à être utilisé!');
        $this->command->line('');
    }
}
