@extends('layouts.app')

{{-- Set the title dynamically based on the unit --}}
@section('title', 'Projets de l\'Unité : ' . $unit->name)

@section('content')
    <div class="container mx-auto px-4">
        
        {{-- HEADER WITH ADD PROJECT BUTTON --}}
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-3xl font-extrabold text-gray-800">
                Projets pour l'Unité : <span class="text-blue-600">{{ $unit->name }}</span>
            </h1>

            {{-- AUTHORIZATION CHECK FOR AGENT --}}
            @if (Auth::user()->role === 'agent')
                <button 
                    onclick="document.getElementById('add-project-modal').classList.remove('hidden')"
                    class="flex items-center bg-green-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-green-700 transition duration-150 text-sm font-medium"
                >
                    <i class="fas fa-plus mr-2"></i> Ajouter Un Projet
                </button>
            @endif
        </div>
        
        <p class="text-gray-600 mb-6 border-b pb-4">
            Liste de tous les projets relevant de cette unité. Cliquez sur un projet pour voir l'avancement détaillé des tâches.
        </p>

        {{-- FILTER SECTION --}}
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                {{-- Type Filter --}}
                <div class="flex-1 min-w-48">
                    <label for="filter-type" class="block text-sm font-medium text-gray-700 mb-1">Type de Projet</label>
                    <select id="filter-type" class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les types</option>
                        <option value="voie">Voie</option>
                        <option value="terrassement">Terrassement</option>
                    </select>
                </div>

                {{-- Status Filter (based on dates) --}}
                <div class="flex-1 min-w-48">
                    <label for="filter-status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select id="filter-status" class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les statuts</option>
                        <option value="section general">À venir</option>
                        <option value="ongoing">En cours</option>
                        <option value="overdue">En retard</option>
                        <option value="completed">Terminé</option>
                    </select>
                </div>

                {{-- Date Range Filter --}}
                <div class="flex-1 min-w-48">
                    <label for="filter-date-from" class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                    <input type="date" id="filter-date-from" class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex-1 min-w-48">
                    <label for="filter-date-to" class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                    <input type="date" id="filter-date-to" class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                {{-- Search --}}
                <div class="flex-1 min-w-48">
                    <label for="filter-search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                    <input type="text" id="filter-search" placeholder="Nom du projet ou section..." class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                {{-- Clear Filters Button --}}
                <div>
                    <button id="clear-filters" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-150">
                        <i class="fas fa-times mr-1"></i> Effacer
                    </button>
                </div>
            </div>
        </div>

        <div id="no-results" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 hidden" role="alert">
            <p class="font-bold">Aucun Projet Trouvé</p>
            <p>Aucun projet ne correspond aux critères de filtrage sélectionnés.</p>
        </div>

        @if ($projects->isEmpty())
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p class="font-bold">Aucun Projet Trouvé</p>
                @if (Auth::user()->role === 'agent')
                <p>En tant qu'Agent, vous pouvez utiliser le bouton ci-dessus pour ajouter un nouveau projet à cette unité.</p>
                @else
                <p>Il n'y a actuellement aucun projet associé à cette unité.</p>
                @endif
            </div>
        @else
            {{-- PROJECTS GRID --}}
            <div id="projects-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach ($projects as $project)
                    {{-- Add data attributes for filtering --}}
                    <a href="{{ route('projects.show', $project) }}" 
                       class="project-card block" 
                       data-type="{{ strtolower($project->type ?? '') }}"
                       data-name="{{ strtolower($project->name) }}"
                       data-section="{{ strtolower($project->section ?? '') }}"
                       data-start-date="{{ $project->start_date }}"
                       data-end-date="{{ $project->end_date }}">
                        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-2xl transition duration-300 transform hover:scale-[1.005] border-l-8 border-gray-400">
                            
                            <h2 class="text-xl font-bold text-gray-800 mb-1">
                                {{ $project->name }}
                            </h2>
                            
                            <p class="text-sm font-medium text-gray-500 mb-3">
                                <span class="uppercase text-xs tracking-wider bg-indigo-100 text-indigo-800 py-1 px-2 rounded-full">{{ $project->type ?? 'Type non défini' }}</span>
                                <span class="ml-2 text-gray-400">|</span>
                                <span class="ml-2">{{ $project->section ?? 'Section: N/A' }}</span>
                            </p>
                            
                            <div class="text-sm text-gray-700 space-y-1 mt-4">
                                <p>
                                    <i class="far fa-calendar-alt text-blue-500 w-5"></i> 
                                    Début : <span class="font-semibold">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : 'N/A' }}</span>
                                </p>
                                <p>
                                    <i class="far fa-calendar-check text-red-500 w-5"></i> 
                                    Fin Prévue : <span class="font-semibold">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : 'N/A' }}</span>
                                </p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
        
        {{-- Back button to Unit Index --}}
        <div class="mt-8">
            <a href="{{ route('units.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 transition duration-200 font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Retour à la liste des unités
            </a>
        </div>
    </div>

    {{-- ADD PROJECT MODAL (HIDDEN BY DEFAULT) --}}
    {{-- This modal needs the Unit ID to associate the new project --}}
    <div id="add-project-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 {{ $errors->any() ? '' : 'hidden' }} flex items-center justify-center z-50 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-xl mx-4">
            
            {{-- Modal Header --}}
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-xl font-bold text-gray-800">Ajouter un nouveau projet pour {{ $unit->name }}</h3>
                <button 
                    onclick="document.getElementById('add-project-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            {{-- Project Creation Form --}}
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                {{-- Crucial hidden input to link the project to the current unit --}}
                <input type="hidden" name="unit_id" value="{{ $unit->id }}">

                {{-- Display validation errors --}}
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form Fields --}}
                <div class="space-y-4">
                    {{-- Project Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom du Projet</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="Nom du projet">
                    </div>

                    {{-- Project Type --}}
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" id="type" required class="w-full p-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                            <option value="" disabled {{ old('type') ? '' : 'selected' }}>Sélectionner le type</option>
                            <option value="voie" {{ old('type') == 'voie' ? 'selected' : '' }}>Voie</option>
                            <option value="terrassement" {{ old('type') == 'terrassement' ? 'selected' : '' }}>Terrassement</option>
                        </select>
                    </div>

                    {{-- Project Section --}}
                    <div>
                        <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                        <input type="text" name="section" id="section" value="{{ old('section') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="Ex: Section Generale">
                    </div>

                    {{-- Dates --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Date de Début</label>
                            <input type="date" name="start_date" id="start_date" required value="{{ old('start_date') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" onchange="updateMinEndDate()">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Date de Fin Prévue</label>
                            <input type="date" name="end_date" id="end_date" required value="{{ old('end_date') }}" class="w-full p-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                        </div>
                        <script>
                            function updateMinEndDate() {
                                const startDate = document.getElementById('start_date').value;
                                const endDateInput = document.getElementById('end_date');
                                if (startDate) {
                                    endDateInput.min = startDate;
                                }
                            }
                        </script>
                    </div>
                </div>
                
                {{-- Action Buttons --}}
                <div class="flex justify-end space-x-3 mt-6">
                    <button 
                        type="button" 
                        onclick="document.getElementById('add-project-modal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-150"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150 font-medium"
                    >
                        Créer le Projet
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript for filtering --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterType = document.getElementById('filter-type');
            const filterStatus = document.getElementById('filter-status');
            const filterDateFrom = document.getElementById('filter-date-from');
            const filterDateTo = document.getElementById('filter-date-to');
            const filterSearch = document.getElementById('filter-search');
            const clearFilters = document.getElementById('clear-filters');
            const projectCards = document.querySelectorAll('.project-card');
            const noResults = document.getElementById('no-results');
            const projectsGrid = document.getElementById('projects-grid');

            function getProjectStatus(startDate, endDate) {
                if (!startDate || !endDate) return 'unknown';
                
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Reset time for accurate comparison
                
                const start = new Date(startDate);
                start.setHours(0, 0, 0, 0);
                
                const end = new Date(endDate);
                end.setHours(0, 0, 0, 0);

                if (today < start) return 'upcoming';
                if (today > end) return 'overdue';
                if (today >= start && today <= end) return 'ongoing';
                return 'completed';
            }

            function filterProjects() {
                const typeFilter = filterType.value.toLowerCase();
                const statusFilter = filterStatus.value;
                const dateFromFilter = filterDateFrom.value;
                const dateToFilter = filterDateTo.value;
                const searchFilter = filterSearch.value.toLowerCase();

                let visibleCount = 0;

                projectCards.forEach(card => {
                    let isVisible = true;

                    // Type filter
                    if (typeFilter && card.dataset.type !== typeFilter) {
                        isVisible = false;
                    }

                    // Status filter
                    if (statusFilter) {
                        const projectStatus = getProjectStatus(card.dataset.startDate, card.dataset.endDate);
                        if (projectStatus !== statusFilter) {
                            isVisible = false;
                        }
                    }

                    // Date range filter
                    if (dateFromFilter && card.dataset.startDate < dateFromFilter) {
                        isVisible = false;
                    }
                    if (dateToFilter && card.dataset.endDate > dateToFilter) {
                        isVisible = false;
                    }

                    // Search filter
                    if (searchFilter) {
                        const projectName = card.dataset.name;
                        const projectSection = card.dataset.section;
                        if (!projectName.includes(searchFilter) && !projectSection.includes(searchFilter)) {
                            isVisible = false;
                        }
                    }

                    // Show/hide card
                    if (isVisible) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show/hide no results message
                if (visibleCount === 0 && projectCards.length > 0) {
                    noResults.classList.remove('hidden');
                    projectsGrid.classList.add('hidden');
                } else {
                    noResults.classList.add('hidden');
                    projectsGrid.classList.remove('hidden');
                }
            }

            // Event listeners
            filterType.addEventListener('change', filterProjects);
            filterStatus.addEventListener('change', filterProjects);
            filterDateFrom.addEventListener('change', filterProjects);
            filterDateTo.addEventListener('change', filterProjects);
            filterSearch.addEventListener('input', filterProjects);

            // Clear filters
            clearFilters.addEventListener('click', function() {
                filterType.value = '';
                filterStatus.value = '';
                filterDateFrom.value = '';
                filterDateTo.value = '';
                filterSearch.value = '';
                filterProjects();
            });
        });
    </script>
@endsection
