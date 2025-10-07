<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Unit;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Initial cleanup
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tasks')->truncate();
        DB::table('projects')->truncate();
        DB::table('units')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- 1. Create a CEO user (Directeur) ---
        $pdg = User::create([
            'username' => 'pdg',
            'password' => Hash::make('pdg'),
            'role' => 'directeur',
        ]);

        // Define the data for the two CSV files
        $filesData = [
            [
                'unit_name' => 'LRS TINDOUF BECHAR',
                'file_path' => 'exemple pour LRS TINDOUF BECHAR - TRAVAUX DE VOIE 1 250 ML .csv',
                'project_name' => 'TRAVAUX DU PK 148+175 AU PK 149+425',
                'data_blocks' => [
                    [
                        'work_name' => 'voie', // Renamed work_type_name to work_name for simplicity
                        'start_row' => 10,
                        'end_row' => 19,
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
                'file_path' => 'exemple pour Drea et Annaba - DREA et ANNABA.csv',
                'project_name' => 'Ligne DREA/ANNABA - Divers Travaux',
                'data_blocks' => [
                    [
                        'work_name' => 'terrassement',
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
                        'work_name' => 'voie',
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

        $i = 0;
        foreach ($filesData as $fileData) {
            // --- 2. Create the Unit and Agent ---
            $unit = Unit::create(['name' => $fileData['unit_name']]);
            $agent = User::create([
                'username' => 'agent_' . $i,
                'password' => Hash::make('agent'),
                'role' => 'agent',
            ]);
            $i++;

            // **FIX**: Get the initial context from the first data block to satisfy NOT NULL constraints.
            $initialBlock = $fileData['data_blocks'][0];
            $initialWorkName = $initialBlock['work_name'];
            $initialSectionName = 'Section Générale';

            // --- 3. Create the Project (linked to the Unit) ---
            $project = Project::create([
                'unit_id' => $unit->id,
                'name' => $fileData['project_name'],
                'start_date' => Carbon::now()->subMonths(3),
                // **Using initial values here to prevent the SQLSTATE[23000] error**
                'type' => $initialWorkName,
                'section' => $initialSectionName,
            ]);

            // Read the CSV file
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

            // --- 4. Loop over the work blocks ---
            foreach ($fileData['data_blocks'] as $block) {

                $workName = $block['work_name'];
                $sectionName = 'Section Générale';

                // Update Project Context: This ensures the project context is set to the current block's context
                // (even if it overwrites the value set in the create call).
                $project->update([
                    'type' => $workName,
                    'section' => $sectionName,
                ]);

                // --- 5. Loop over the tasks for insertion ---
                for ($j = $block['start_row']; $j <= $block['end_row']; $j++) {
                    if (!isset($rows[$j])) continue;

                    $row = array_map('trim', $rows[$j]);

                    $designation = $row[$block['designation_col']] ?? null;
                    $unite = $row[$block['unit_col']] ?? null;

                    // Convert planned, cumulative, and daily realized to floats
                    $prevu = (float) str_replace(',', '.', $row[$block['prevu_col']] ?? 0);
                    $cumulPrecedent = (float) str_replace(',', '.', $row[$block['cumul_prev_col']] ?? 0);
                    $realiseJour = (float) str_replace(',', '.', $row[$block['jour_col']] ?? 0);

                    // CALCULATE THE TOTAL ACCOMPLISHED QUANTITY
                    $totalAccomplished = $cumulPrecedent + $realiseJour;

                    // Skip empty rows, headers, or lines without planned quantity
                    if (empty($designation) || str_contains(strtoupper($designation), 'DESIGNATION') || $prevu <= 0) {
                        continue;
                    }

                    // Create the Task (Task does not hold work type/section, only project_id)
                    $task = Task::create([
                        'project_id' => $project->id,
                        'task' => $designation,
                        'planned' => $prevu,
                        'unit_measure' => $unite,
                        'accomplished_quantity' => $totalAccomplished,
                    ]);
                }
            }
        }
    }
}
