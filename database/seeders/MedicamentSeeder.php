<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicament;

class MedicamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicaments = [
            [
                'nom' => 'Paracétamol 500mg',
                'prix' => 2.50,
                'stock' => 100,
                'status' => 'sans'
            ],
            [
                'nom' => 'Ibuprofène 400mg',
                'prix' => 3.20,
                'stock' => 75,
                'status' => 'sans'
            ],
            [
                'nom' => 'Amoxicilline 500mg',
                'prix' => 8.90,
                'stock' => 50,
                'status' => 'avec'
            ],
            [
                'nom' => 'Doliprane 1000mg',
                'prix' => 4.10,
                'stock' => 120,
                'status' => 'sans'
            ],
            [
                'nom' => 'Azithromycine 250mg',
                'prix' => 12.50,
                'stock' => 30,
                'status' => 'avec'
            ],
            [
                'nom' => 'Aspirin 500mg',
                'prix' => 1.80,
                'stock' => 200,
                'status' => 'sans'
            ],
            [
                'nom' => 'Oméprazole 20mg',
                'prix' => 6.30,
                'stock' => 60,
                'status' => 'avec'
            ],
            [
                'nom' => 'Cetirizine 10mg',
                'prix' => 3.80,
                'stock' => 90,
                'status' => 'sans'
            ],
            [
                'nom' => 'Prednisolone 5mg',
                'prix' => 15.20,
                'stock' => 25,
                'status' => 'avec'
            ],
            [
                'nom' => 'Vitamine C 1000mg',
                'prix' => 5.50,
                'stock' => 150,
                'status' => 'sans'
            ]
        ];

        foreach ($medicaments as $medicament) {
            Medicament::create($medicament);
        }
    }
}