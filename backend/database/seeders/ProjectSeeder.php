<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\Project;
use App\Models\Task;
use App\Models\Realization;
use App\Models\User;
use App\Models\WorkType;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Utilisation des NOUVEAUX noms de tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('units')->truncate();
        DB::table('projects')->truncate();
        DB::table('users')->truncate();
        DB::table('work_types')->truncate();
        DB::table('sections')->truncate();
        DB::table('tasks')->truncate();
        DB::table('realizations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- 1. Crée un utilisateur PDG (Directeur) ---
        $pdg = User::create([
            'username' => 'pdg@app.com',
            'password' => Hash::make('password'),
            'role' => 'directeur', // Dans l'ENUM de la DB: 'directeur' ou 'agent'
        ]);

        // ATTENTION : La table 'users' dans vos migrations ne contient pas de 'unit_id'. 
        // L'Agent ne sera donc pas lié à l'Unité dans la DB pour l'instant.

        // Définir les données des deux fichiers CSV
        $filesData = [
            // FICHIER 1: LRS TINDOUF BECHAR
            [
                'unit_name' => 'LRS TINDOUF BECHAR',
                'file_path' => 'exemple pour LRS TINDOUF BECHAR.xlsx - TRAVAUX DE VOIE 1 250 ML .csv',
                'project_name' => 'TRAVAUX DU PK 148+175 AU PK 149+425',
                'data_blocks' => [
                    [
                        'type' => 'TRAVAUX DE VOIE',
                        'start_row' => 10,
                        'end_row' => 19, // Déterminé par l'analyse du fichier
                        'designation_col' => 0,
                        'unit_col' => 1,
                        'prevu_col' => 2,
                        'cumul_prev_col' => 3,
                        'jour_col' => 4,
                        'date' => '2025-05-04',
                    ]
                ]
            ],
            // FICHIER 2: DREA et ANNABA
            [
                'unit_name' => 'DREA et ANNABA',
                'file_path' => 'exemple pour Drea et Annaba.xlsx - DREA et ANNABA.csv',
                'project_name' => 'Ligne DREA/ANNABA - Divers Travaux',
                'data_blocks' => [
                    [
                        'type' => 'TRAVAUX DE TERRASSEMENT',
                        'start_row' => 9,
                        'end_row' => 14,
                        'designation_col' => 0,
                        'unit_col' => 1,
                        'prevu_col' => 2,
                        'cumul_prev_col' => 4,
                        'jour_col' => 5,
                        'date' => '2025-07-02',
                    ],
                    [
                        'type' => 'TRAVAUX DE VOIE',
                        'start_row' => 18,
                        'end_row' => 23,
                        'designation_col' => 0,
                        'unit_col' => 1,
                        'prevu_col' => 2,
                        'cumul_prev_col' => 3,
                        'jour_col' => 4,
                        'date' => '2025-07-02',
                    ],
                ]
            ],
        ];

        foreach ($filesData as $fileData) {
            // --- 2. Création de l'Unité et de l'Agent ---
            $unit = Unit::create(['name' => $fileData['unit_name']]);

            $agent = User::create([
                'username' => strtolower(str_replace(' ', '', $unit->name)) . '@app.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
            ]);

            // --- 3. Création du Projet (lié à l'Unité) ---
            $project = Project::create([
                'unit_id' => $unit->id,
                'name' => $fileData['project_name'],
                'start_date' => Carbon::now()->subMonths(3),
            ]);

            // Lecture du fichier CSV
            $csvFile = storage_path('app/uploads/' . $fileData['file_path']);
            if (!file_exists($csvFile)) {
                $this->command->warn("CSV file not found: " . $fileData['file_path']);
                continue;
            }
            $rows = [];
            if (($handle = fopen($csvFile, "r")) !== false) {
                while (($data = fgetcsv($handle)) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }

            // --- 4. Boucler sur les blocs de travail (WorkType) ---
        }
    }
}
