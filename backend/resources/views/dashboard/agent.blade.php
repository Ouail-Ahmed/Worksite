@extends('layouts.app')

@section('title', 'Tableau de Bord Agent')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-4xl font-extrabold text-gray-800 mb-6 border-b pb-3">
        Vue d'ensemble des Unités
    </h1>

    <p class="text-gray-600 mb-8">
        Bienvenue, {{ $user->username }}. Naviguez entre les unités ci-dessous pour consulter et mettre à jour l'avancement de chaque projet et tâche.
    </p>

    @if (!$units->isEmpty())
        <!-- Filtering Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-filter mr-2"></i>Filtres et Recherche
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                    <input type="text" id="searchFilter" placeholder="Nom d'unité ou projet..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                
                <!-- Project Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de Projet</label>
                    <select id="typeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Tous les types</option>
                        <option value="voie">Voie</option>
                        <option value="terrassement">Terrassement</option>
                    </select>
                </div>
                
                <!-- Progress Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Avancement</label>
                    <select id="progressFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Tous les avancements</option>
                        <option value="0-25">0% - 25%</option>
                        <option value="26-50">26% - 50%</option>
                        <option value="51-75">51% - 75%</option>
                        <option value="76-100">76% - 100%</option>
                    </select>
                </div>
                
                <!-- Task Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut des Tâches</label>
                    <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Tous les statuts</option>
                        <option value="completed">Terminées</option>
                        <option value="inprogress">En cours</option>
                        <option value="notstarted">Non commencées</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-between items-center mt-4">
                <button id="clearFilters" class="text-sm text-gray-600 hover:text-gray-800 transition duration-150">
                    <i class="fas fa-times mr-1"></i>Effacer les filtres
                </button>
                <div id="filterResults" class="text-sm text-gray-600">
                    <span id="visibleCount">{{ $units->count() }}</span> unité(s) affichée(s)
                </div>
            </div>
        </div>
    @endif

    @if ($units->isEmpty())
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg" role="alert">
            <p class="font-bold">Aucune Unité Trouvée</p>
            <p>Il n'y a actuellement aucune unité définie dans le système.</p>
        </div>
    @else
        <!-- No Results Message (Initially Hidden) -->
        <div id="noResults" class="hidden bg-gray-100 border border-gray-300 text-gray-600 p-6 rounded-lg text-center">
            <i class="fas fa-search text-gray-400 text-3xl mb-3"></i>
            <p class="font-medium">Aucun résultat trouvé</p>
            <p class="text-sm">Essayez de modifier vos critères de recherche.</p>
        </div>

        <!-- Conteneur d'Accordéon pour les Unités -->
        <div class="space-y-6" id="unitsContainer">
            @foreach ($units as $unit)
                <!-- Accordéon Unité -->
                <details class="unit-item bg-white rounded-xl shadow-2xl overflow-hidden group border border-gray-200" 
                         data-unit-name="{{ strtolower($unit->name) }}"
                         data-unit-id="{{ $unit->id }}">
                    <summary class="flex items-center justify-between p-5 cursor-pointer bg-gray-50 hover:bg-gray-100 transition duration-200">
                        <div class="flex items-center space-x-4">
                            <span class="text-3xl text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                                </svg>
                            </span>
                            <h2 class="text-2xl font-bold text-gray-800">
                                {{ $unit->name }} 
                                <span class="text-sm font-medium text-blue-500 ml-2">({{ $unit->projects_count ?? ($unit->projects->count() ?? 0) }} Projets)</span>
                            </h2>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform transition duration-300 group-open:rotate-180 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>

                    <div class="p-6 border-t border-gray-200 bg-white">
                        @php
                            $projectsByType = $unit->projects->groupBy('type');
                            $types = ['voie', 'terrassement'];
                        @endphp

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            @foreach ($types as $type)
                                <div class="project-type-section border p-4 rounded-lg shadow-inner @if($type === 'voie') border-purple-300 bg-purple-50 @else border-emerald-300 bg-emerald-50 @endif" 
                                     data-project-type="{{ $type }}">
                                    <h3 class="text-xl font-semibold mb-4 flex items-center @if($type === 'voie') text-purple-700 @else text-emerald-700 @endif">
                                        @if($type === 'voie')
                                            <i class="fas fa-road mr-2"></i> Section {{ $type }}
                                        @else
                                            <i class="fas fa-mountain mr-2"></i> Section {{ $type }}
                                        @endif
                                        <span class="ml-2 px-2 py-0.5 text-xs font-bold rounded-full @if($type === 'voie') bg-purple-200 text-purple-800 @else bg-emerald-200 text-emerald-800 @endif">
                                            {{ isset($projectsByType[$type]) ? $projectsByType[$type]->count() : 0 }} Projets
                                        </span>
                                    </h3>
                                    
                                    @if (isset($projectsByType[$type]))
                                        <div class="space-y-4">
                                            @foreach ($projectsByType[$type] as $project)
                                                @php
                                                    $totalPlanned = $project->tasks->sum('planned') ?? 0;
                                                    $totalAchieved = $project->tasks->sum('total_achieved') ?? 0;
                                                    $progress = $totalPlanned > 0 ? round(($totalAchieved / $totalPlanned) * 100) : 0;
                                                @endphp
                                                
                                                <details class="project-item bg-white p-3 rounded-lg shadow-md border-l-4 @if($type === 'voie') border-purple-500 @else border-emerald-500 @endif"
                                                         data-project-name="{{ strtolower($project->name) }}"
                                                         data-project-progress="{{ $progress }}"
                                                         data-project-type="{{ $type }}">
                                                    <summary class="font-bold text-gray-800 cursor-pointer flex justify-between items-center text-md">
                                                        {{ $project->name }} ({{ $project->tasks->count() ?? 0 }} Tâches)
                                                        <span class="text-xs font-normal text-gray-500 ml-2">
                                                            Avancement: {{ $progress }}%
                                                        </span>
                                                    </summary>
                                                    
                                                    <!-- Project Info Section -->
                                                    <div class="mt-3 bg-gray-50 p-3 rounded-lg border">
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                                            <p class="text-gray-600">
                                                                <span class="font-semibold">Période:</span> 
                                                                {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') : 'N/A' }} 
                                                                au 
                                                                {{ $project->finish_date ? \Carbon\Carbon::parse($project->finish_date)->format('d/m/Y') : 'N/A' }}
                                                            </p>
                                                            <p class="text-gray-600">
                                                                <span class="font-semibold">Section:</span> {{ $project->section ?? 'N/A' }}
                                                            </p>
                                                        </div>
                                                        
                                                        <div class="mt-2 flex justify-between items-center">
                                                            <div class="text-xs text-gray-600">
                                                                <span class="font-semibold">Planifié:</span> {{ number_format($totalPlanned, 2) }} |
                                                                <span class="font-semibold">Réalisé:</span> {{ number_format($totalAchieved, 2) }}
                                                            </div>
                                                            <div class="flex items-center">
                                                                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold @if($progress >= 75) text-green-600 @elseif($progress >= 50) text-yellow-600 @else text-red-600 @endif">
                                                                    {{ $progress }}%
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Task List Section -->
                                                    <div class="mt-4">
                                                        <div class="flex items-center justify-between mb-3">
                                                            <h4 class="text-sm font-semibold text-gray-700">
                                                                <i class="fas fa-tasks mr-1"></i>
                                                                Tâches du Projet ({{ $project->tasks->count() ?? 0 }})
                                                            </h4>
                                                        </div>
                                                        
                                                        @forelse ($project->tasks as $task)
                                                            @php
                                                                $taskStatus = 'notstarted';
                                                                if ($task->finished_date) {
                                                                    $taskStatus = 'completed';
                                                                } elseif ($task->total_achieved > 0) {
                                                                    $taskStatus = 'inprogress';
                                                                }
                                                            @endphp
                                                            
                                                            <div class="task-item bg-white border border-gray-200 rounded-md p-3 mb-2 shadow-sm"
                                                                 data-task-status="{{ $taskStatus }}">
                                                                <div class="flex items-start justify-between">
                                                                    <div class="flex-1">
                                                                        <div class="flex items-center mb-1">
                                                                            <span class="w-2 h-2 mr-2 rounded-full @if($task->finished_date) bg-green-500 @elseif($task->total_achieved > 0) bg-yellow-500 @else bg-red-500 @endif flex-shrink-0"></span>
                                                                            <h5 class="font-medium text-sm text-gray-800">{{ $task->task }}</h5>
                                                                        </div>
                                                                        
                                                                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 mt-2">
                                                                            <div>
                                                                                <span class="font-medium">Unité:</span> {{ $task->unit_measure ?? 'N/A' }}
                                                                            </div>
                                                                            <div>
                                                                                <span class="font-medium">Planifié:</span> {{ number_format($task->planned ?? 0, 2) }}
                                                                            </div>
                                                                            <div>
                                                                                <span class="font-medium">Réalisé:</span> 
                                                                                <span class="text-green-600 font-semibold">{{ number_format($task->total_achieved ?? 0, 2) }}</span>
                                                                            </div>
                                                                            <div>
                                                                                <span class="font-medium">Statut:</span> 
                                                                                @if($task->finished_date)
                                                                                    <span class="text-green-600 font-medium">Terminée</span>
                                                                                @elseif($task->total_achieved > 0)
                                                                                    <span class="text-yellow-600 font-medium">En cours</span>
                                                                                @else
                                                                                    <span class="text-red-600 font-medium">Non commencée</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        @php
                                                                            $taskProgress = $task->planned > 0 ? (($task->total_achieved ?? 0) / $task->planned) * 100 : 0;
                                                                        @endphp
                                                                        <div class="mt-2">
                                                                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                                                                <span>Progression</span>
                                                                                <span>{{ number_format($taskProgress, 1) }}%</span>
                                                                            </div>
                                                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                                                <div class="@if($taskProgress >= 100) bg-green-500 @elseif($taskProgress >= 50) bg-yellow-500 @else bg-red-500 @endif h-1.5 rounded-full" style="width: {{ min($taskProgress, 100) }}%"></div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        @if($task->created_at)
                                                                        <div class="text-xs text-gray-500 mt-2">
                                                                            Créée le: {{ \Carbon\Carbon::parse($task->created_at)->format('d/m/Y') }}
                                                                            @if($task->finished_date)
                                                                                | Terminée le: {{ \Carbon\Carbon::parse($task->finished_date)->format('d/m/Y') }}
                                                                            @endif
                                                                        </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="text-center py-4 text-gray-500 text-sm italic bg-gray-50 rounded-lg">
                                                                <i class="fas fa-inbox text-gray-400 mb-2"></i>
                                                                <p>Aucune tâche définie pour ce projet.</p>
                                                            </div>
                                                        @endforelse
                                                    </div>

                                                    <div class="mt-4 flex justify-end border-t pt-3">
                                                        <a href="{{ route('projects.show', $project->id) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition duration-150">
                                                            Détails du Projet &rarr;
                                                        </a>
                                                    </div>
                                                </details>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-gray-500 italic text-sm">Aucun projet de type "{{ $type }}" trouvé pour cette unité.</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endforeach
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchFilter = document.getElementById('searchFilter');
    const typeFilter = document.getElementById('typeFilter');
    const progressFilter = document.getElementById('progressFilter');
    const statusFilter = document.getElementById('statusFilter');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const visibleCount = document.getElementById('visibleCount');
    const noResults = document.getElementById('noResults');
    const unitsContainer = document.getElementById('unitsContainer');

    function applyFilters() {
        const searchTerm = searchFilter.value.toLowerCase();
        const selectedType = typeFilter.value;
        const selectedProgress = progressFilter.value;
        const selectedStatus = statusFilter.value;

        const units = document.querySelectorAll('.unit-item');
        let visibleUnits = 0;

        units.forEach(unit => {
            const unitName = unit.dataset.unitName;
            const projects = unit.querySelectorAll('.project-item');
            let hasVisibleProjects = false;

            projects.forEach(project => {
                const projectName = project.dataset.projectName;
                const projectType = project.dataset.projectType;
                const projectProgress = parseInt(project.dataset.projectProgress);
                const tasks = project.querySelectorAll('.task-item');

                // Check search filter
                const matchesSearch = !searchTerm || 
                    unitName.includes(searchTerm) || 
                    projectName.includes(searchTerm);

                // Check type filter
                const matchesType = !selectedType || projectType === selectedType;

                // Check progress filter
                let matchesProgress = true;
                if (selectedProgress) {
                    const [min, max] = selectedProgress.split('-').map(Number);
                    matchesProgress = projectProgress >= min && projectProgress <= max;
                }

                // Check status filter (if project has tasks with matching status)
                let matchesStatus = true;
                if (selectedStatus && tasks.length > 0) {
                    matchesStatus = Array.from(tasks).some(task => 
                        task.dataset.taskStatus === selectedStatus
                    );
                } else if (selectedStatus && tasks.length === 0) {
                    matchesStatus = false;
                }

                const isVisible = matchesSearch && matchesType && matchesProgress && matchesStatus;
                
                if (isVisible) {
                    project.style.display = 'block';
                    hasVisibleProjects = true;
                } else {
                    project.style.display = 'none';
                }
            });

            // Show/hide project type sections
            const typeSections = unit.querySelectorAll('.project-type-section');
            typeSections.forEach(section => {
                const visibleProjectsInSection = section.querySelectorAll('.project-item[style="display: block"], .project-item:not([style*="display: none"])').length;
                
                if (selectedType && section.dataset.projectType !== selectedType) {
                    section.style.display = 'none';
                } else if (visibleProjectsInSection > 0) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });

            // Show/hide units
            if (hasVisibleProjects) {
                unit.style.display = 'block';
                visibleUnits++;
            } else {
                unit.style.display = 'none';
            }
        });

        // Update visible count
        visibleCount.textContent = visibleUnits;

        // Show/hide no results message
        if (visibleUnits === 0) {
            noResults.classList.remove('hidden');
            unitsContainer.classList.add('hidden');
        } else {
            noResults.classList.add('hidden');
            unitsContainer.classList.remove('hidden');
        }
    }

    function clearFilters() {
        searchFilter.value = '';
        typeFilter.value = '';
        progressFilter.value = '';
        statusFilter.value = '';
        
        // Show all items
        document.querySelectorAll('.unit-item, .project-item, .project-type-section').forEach(item => {
            item.style.display = 'block';
        });
        
        noResults.classList.add('hidden');
        unitsContainer.classList.remove('hidden');
        visibleCount.textContent = document.querySelectorAll('.unit-item').length;
    }

    // Event listeners
    searchFilter.addEventListener('input', applyFilters);
    typeFilter.addEventListener('change', applyFilters);
    progressFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    clearFiltersBtn.addEventListener('click', clearFilters);
});
</script>

@endsection