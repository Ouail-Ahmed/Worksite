@extends('layouts.app')

@section('title', 'Détails du Projet : ' . $project->name)

@push('styles')
    {{-- Assuming this is a local script that loads Tailwind CSS --}}
{{--     <script src="{{ asset('js/tailwind-cdn.js') }}"></script> --}}
@endpush

@section('content')
    <div class="container mx-auto">
        {{-- Notification Area (Toast) --}}
        <div id="notification-container" class="fixed top-4 right-4 z-[100] space-y-2">
            {{-- Notifications will be inserted here --}}
        </div>

        <div class="flex items-center justify-between mb-6 border-b pb-3">
            <h1 class="text-3xl font-extrabold text-gray-800">
                Projet : <span class="text-blue-600">{{ $project->name }}</span>
            </h1>
            <a href="{{ route('units.projects', $project->unit) }}" class="text-sm font-medium text-gray-500 hover:text-blue-600">
                <i class="fas fa-arrow-left mr-1"></i> Retour aux projets de l'unité
            </a>
        </div>

        {{-- Global Project Progress Card (IDs added for JS update) --}}
        <div class="bg-white p-6 rounded-xl shadow-lg mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex-1 space-y-2">
                <p class="text-sm text-gray-600"><span class="font-semibold">Unité :</span> {{ $project->unit->name }}</p>
                <p class="text-sm text-gray-600"><span class="font-semibold">Période :</span> {{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($project->finish_date)->format('d/m/Y') }}</p>
                <p class="text-sm text-gray-600"><span class="font-semibold">Type/Section :</span> {{ $project->type }} / {{ $project->section }}</p>
            </div>
            
        </div>

        {{-- Task Header with Add Button --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Tâches du Projet (<span id="task-count">{{ $project->tasks->count() }}</span>)</h2>
            @if (Auth::check() && Auth::user()->role === 'agent')
            <button onclick="openAddTaskModal({{ $project->id }})" class="flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-3 rounded-lg shadow transition duration-150 text-sm">
                <i class="fas fa-plus mr-1"></i> Ajouter Tâche
            </button>
            @endif
        </div>

        {{-- Filter Section (unchanged) --}}
        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center space-x-2">
                <label for="search-task" class="text-sm font-medium text-gray-700">Rechercher:</label>
                <div class="flex">
                <input type="text" id="search-task" placeholder="Nom de la tâche..." 
                class="px-3 py-2 border border-gray-300 rounded-l-md text-sm focus:ring-blue-500 focus:border-blue-500">
                <button onclick="filterTasks()" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white border border-blue-600 rounded-r-md text-sm transition duration-150">
                    <i class="fas fa-search"></i>
                </button>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <label for="filter-status" class="text-sm font-medium text-gray-700">Statut:</label>
                <select id="filter-status" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="all">Tous</option>
                <option value="completed">Terminées</option>
                <option value="in-progress">En cours</option>
                <option value="not-started">Non commencées</option>
                </select>
            </div>
            
            <div class="flex items-center space-x-2">
                <label for="filter-unit" class="text-sm font-medium text-gray-700">Unité:</label>
                <select id="filter-unit" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="all">Toutes</option>
                @foreach($project->tasks->unique('unit_measure') as $task)
                    <option value="{{ $task->unit_measure }}">{{ $task->unit_measure }}</option>
                @endforeach
                </select>
            </div>
            
            <div class="flex items-center space-x-2">
                <label for="filter-period-start" class="text-sm font-medium text-gray-700">Du:</label>
                <input type="date" id="filter-period-start" 
                class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex items-center space-x-2">
                <label for="filter-period-end" class="text-sm font-medium text-gray-700">Au:</label>
                <input type="date" id="filter-period-end" 
                class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex items-center space-x-2">
                <label for="sort-by" class="text-sm font-medium text-gray-700">Trier par:</label>
                <select id="sort-by" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="name">Nom</option>
                <option value="created">Date création</option>
                <option value="planned">Quantité planifiée</option>
                <option value="progress">Avancement</option>
                </select>
            </div>
            
            <button onclick="clearFilters()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded-md transition duration-150">
                <i class="fas fa-times mr-1"></i> Effacer
            </button>
            </div>
        </div>

        {{-- Tasks Table (unchanged) --}}
        <div class="overflow-x-auto bg-white rounded-xl shadow-lg">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tâche</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unité</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Planifié</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Réalisé</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Progrès %</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progrès J.</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date Créée</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date Terminée</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="tasks-table-body">
            @forelse ($project->tasks as $task)
            @php
                $progressPercentage = $task->planned > 0 ? ($task->total_achieved / $task->planned * 100) : 0;
                $progressPercentage = min($progressPercentage, 100); // Cap at 100%
            @endphp
            <tr id="task-row-{{ $task->id }}" class="task-row" 
                data-task-name="{{ strtolower($task->task) }}"
                data-unit="{{ $task->unit_measure }}"
                data-status="{{ $task->finished_date ? 'completed' : ($task->total_achieved > 0 ? 'in-progress' : 'not-started') }}"
                data-created="{{ $task->created_at }}"
                data-planned="{{ $task->planned }}"
                data-achieved="{{ $task->total_achieved }}"
                data-progress="{{ $progressPercentage }}">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $task->task }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">{{ $task->unit_measure }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700" data-planned="{{ $task->planned }}">{{ number_format($task->planned, 2) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-600 font-bold task-achieved-total" data-total-achieved="{{ $task->total_achieved }}">{{ number_format($task->total_achieved, 2) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm task-progress-cell" data-progress="{{ $progressPercentage }}">
                <div class="flex items-center justify-center">
                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%"></div>
                </div>
                <span class="text-xs font-medium 
                {{ $progressPercentage >= 100 ? 'text-green-600' : ($progressPercentage > 0 ? 'text-blue-600' : 'text-gray-500') }}">
                {{ number_format($progressPercentage, 1) }}%
                </span>
                </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-blue-600 task-daily-progress" data-daily-progress="{{ $task->accomplished_quantity }}">{{ number_format($task->accomplished_quantity, 2) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">{{ \Carbon\Carbon::parse($task->created_at)->format('d/m/Y') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 task-finished-date-cell">
                @if($task->finished_date)
                <span class="text-green-600 font-medium">{{ \Carbon\Carbon::parse($task->finished_date)->format('d/m/Y') }}</span>
                @elseif($task->total_achieved > 0)
                <span class="text-orange-500">En cours</span>
                @else
                <span class="text-gray-500">Non commencée</span>
                @endif
                </td>
                
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center justify-center space-x-1">
                @if (Auth::check() && Auth::user()->role === 'agent')
                <button 
                onclick="openProgressModal(this)"
                data-task-id="{{ $task->id }}"
                data-task-name="{{ addslashes($task->task) }}"
                data-planned="{{ $task->planned }}"
                data-achieved="{{ $task->total_achieved }}"
                class="text-indigo-600 hover:text-indigo-900 transition duration-150 px-2 py-1 border border-indigo-200 rounded-md text-xs hover:bg-indigo-50"
                title="Reporter Avancement"
                >
                <i class="fas fa-plus"></i>
                </button>
                <button 
                onclick="confirmDeleteTask({{ $task->id }}, '{{ addslashes($task->task) }}')"
                class="text-red-600 hover:text-red-900 transition duration-150 px-2 py-1 border border-red-200 rounded-md text-xs hover:bg-red-50"
                title="Supprimer"
                >
                <i class="fas fa-trash"></i>
                </button>
                @endif
                <button 
                onclick="openProgressHistoryModal({{ $task->id }}, '{{ addslashes($task->task) }}')"
                class="text-blue-600 hover:text-blue-900 transition duration-150 px-2 py-1 border border-blue-200 rounded-md text-xs hover:bg-blue-50"
                title="Historique"
                >
                <i class="fas fa-history"></i>
                </button>
                @if (Auth::check() && Auth::user()->role !== 'agent')
                <span class="text-gray-400 text-xs" title="Lecture Seule">
                <i class="fas fa-eye"></i>
                </span>
                @endif
                </div>
                </td>
            </tr>
            @empty
            <tr id="no-tasks-row">
                <td colspan="9" class="px-6 py-4 text-center text-gray-500">Aucune tâche n'a été définie pour ce projet.</td>
            </tr>
            @endforelse
            </tbody>
            </table>
        </div>
        </div>


    {{-- MODAL REPORTER AVANCEMENT (unchanged) --}}
    <div id="progress-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-md">
            <h3 class="text-xl font-bold mb-4 border-b pb-2">Reporter l'Avancement</h3>
            <p id="modal-task-name" class="text-gray-600 mb-4"></p>
            
            <form id="progress-form" data-task-id="">
                @csrf
                @method('PUT')
                <input type="hidden" name="task_id" id="modal-task-id">

                <div class="mb-4">
                    <label for="daily_quantity" class="block text-sm font-medium text-gray-700">Quantité Réalisée Aujourd'hui</label>
                    <input type="number" step="0.01" min="0" name="daily_quantity" id="daily_quantity" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-red-500 mt-1 hidden" id="error-message"></p>
                    <p class="text-xs text-gray-500 mt-1">Maximum possible: <span id="max-quantity" class="font-semibold"></span></p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeProgressModal()" class="px-4 py-2 text-gray-600 border rounded-md hover:bg-gray-50">Annuler</button>
                    <button type="submit" id="save-progress-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: ADD TASK (unchanged) --}}
    <div id="add-task-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-md">
            <h3 class="text-xl font-bold mb-4 border-b pb-2">Ajouter une Nouvelle Tâche</h3>
            
            <form id="add-task-form">
                @csrf
                <input type="hidden" name="project_id" id="add-task-project-id" value="{{ $project->id }}"> 

                <div class="mb-4">
                    <label for="task_name" class="block text-sm font-medium text-gray-700">Nom de la Tâche</label>
                    <input type="text" name="task_name" id="task_name" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="planned_quantity" class="block text-sm font-medium text-gray-700">Quantité Planifiée (Prévu)</label>
                    <input type="number" step="0.01" min="0" name="planned_quantity" id="planned_quantity" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="unit_measure" class="block text-sm font-medium text-gray-700">Unité de Mesure (ML, U, etc.)</label>
                    <input type="text" name="unit_measure" id="unit_measure" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <p class="text-xs text-red-500 mt-1 hidden" id="add-error-message"></p>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeAddTaskModal()" class="px-4 py-2 text-gray-600 border rounded-md hover:bg-gray-50">Annuler</button>
                    <button type="submit" id="create-task-btn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150">Créer Tâche</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// --- Global Initialization (Hoisted to be available everywhere) ---
const csrfMeta = document.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

// State tracking for modals/forms
let currentTaskPlanned = 0;
let currentTaskAchieved = 0;

function createLoadingModal(modalId, loadingMessage = 'Chargement en cours...') {
    const modal = document.createElement('div');
    modal.id = modalId;
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50 transition-opacity duration-300';
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-4xl max-h-[80vh] overflow-y-auto transform scale-95 transition-transform duration-300">
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-4 border-indigo-600"></div>
                <span class="ml-4 text-lg font-medium text-gray-700">${loadingMessage}</span>
            </div>
        </div>
    `;
    return modal;
}
// --- Task Deletion Functions ---

async function confirmDeleteTask(taskId, taskName) {
    const confirmed = confirm(`Êtes-vous sûr de vouloir supprimer la tâche "${taskName}" ?\n\nCette action est irréversible et supprimera également tout l'historique d'avancement associé.`);
    
    if (confirmed) {
        await deleteTask(taskId, taskName);
    }
}

async function deleteTask(taskId, taskName) {
    try {
        await apiFetch(`/tasks/${taskId}`, {
            method: 'DELETE'
        });

        // Remove the task row from the DOM
        const taskRow = document.getElementById(`task-row-${taskId}`);
        if (taskRow) {
            taskRow.remove();
        }

        // Update task count
        const remainingTasks = document.querySelectorAll('.task-row').length;
        document.getElementById('task-count').textContent = remainingTasks;

        // Show "no tasks" row if no tasks remain
        if (remainingTasks === 0) {
            const tableBody = document.getElementById('tasks-table-body');
            const noTasksRow = document.createElement('tr');
            noTasksRow.id = 'no-tasks-row';
            noTasksRow.innerHTML = '<td colspan="9" class="px-6 py-4 text-center text-gray-500">Aucune tâche n\'a été définie pour ce projet.</td>';
            tableBody.appendChild(noTasksRow);
        }

        // Refresh filters to update counts and options
        filterTasks();
        
        showNotification(`Tâche "${taskName}" supprimée avec succès !`, 'success');
        
    } catch (error) {
        const errorText = error.message || 'Erreur lors de la suppression de la tâche.';
        showNotification(`Échec de la suppression: ${errorText}`, 'error');
    }
}
async function apiFetch(url, options = {}) {
    const defaultHeaders = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
    };

    if (options.body && typeof options.body !== 'string') {
        options.body = JSON.stringify(options.body);
        defaultHeaders['Content-Type'] = 'application/json';
    }

    const response = await fetch(url, {
        ...options,
        headers: {
            ...defaultHeaders,
            ...options.headers
        }
    });

    // Handle non-JSON response gracefully (important check)
    const contentType = response.headers.get('content-type');
    let data;

    if (contentType && contentType.includes('application/json')) {
        data = await response.json();
    } else {
        const text = await response.text();
        if (!response.ok) {
            console.error('Expected JSON but received non-OK response text:', text);
            throw new Error(`Erreur du serveur (Statut ${response.status}): ${text.substring(0, 100)}...`);
        }
        // If it's a 2xx response but not JSON, we treat it as an empty/success response.
        data = {};
    }

    if (!response.ok) {
        throw new Error(data.message || 'Erreur inconnue lors de l\'opération.');
    }

    return data;
}

async function openProgressHistoryModal(taskId, taskName) {
    const MODAL_ID = 'history-modal';
    const existingModal = document.getElementById(MODAL_ID);

    // 1. Remove any previous modal instance and insert loading state
    if (existingModal) existingModal.remove();
    document.body.appendChild(createLoadingModal(MODAL_ID, 'Chargement de l\'historique...'));

    const modal = document.getElementById(MODAL_ID);

    try {
        const data = await apiFetch(`/tasks/${taskId}/history`, { method: 'GET' });
        const historyData = data.history || [];

        // 2. Build the final content HTML
        const historyHtml = buildHistoryContent(historyData);
        
        // 3. Update the modal with final content
        modal.innerHTML = buildModalStructure(taskName, historyHtml, MODAL_ID);

        // Optional: Add a smooth scale-in animation on the new content
        const innerContent = modal.querySelector('.shadow-2xl');
        if (innerContent) {
            innerContent.style.transform = 'scale(1)';
            innerContent.style.opacity = '1';
        }

    } catch (error) {
        console.error('Erreur lors du chargement de l\'historique:', error);
        // Display error state in the modal
        modal.innerHTML = buildErrorStructure(taskName, error.message);
        showNotification(`Échec du chargement: ${error.message}`, 'error');
    }
}

/**
 * Builds the HTML content for the history records.
 * @param {Array<Object>} records
 * @returns {string} The HTML string for the history list or empty state.
 */
function buildHistoryContent(records) {
    if (records.length === 0) {
        return `
            <div class="p-6 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 rounded-lg">
                <p class="font-medium">Aucun historique d'avancement n'a été enregistré pour cette tâche.</p>
            </div>
        `;
    }

    // Map records to list items
    const historyItems = records.map(record => {
        const userName = record.user?.username || 'Système';
        const quantity = parseFloat(record.realise_jour);
        const date = new Date(record.date_saisie);

        return `
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 p-4 hover:bg-indigo-50 transition duration-150 ease-in-out">
                <!-- User (Reported By) -->
                <div class="col-span-2 md:col-span-1 flex items-center">
                    <span class="md:hidden font-semibold text-gray-600 mr-2">Par:</span>
                    <span class="font-medium text-indigo-700">${userName}</span>
                </div>

                <!-- Reported Amount (Right aligned) -->
                <div class="col-span-1 flex items-center justify-end">
                    <span class="md:hidden font-semibold text-gray-600 mr-2">Qté:</span>
                    <span class="text-lg font-mono text-gray-900">${quantity.toLocaleString('fr-FR', { minimumFractionDigits: 2 })}</span>
                </div>

                <!-- Date & Time Logged (Right aligned) -->
                <div class="col-span-1 flex items-center justify-end">
                    <span class="md:hidden font-semibold text-gray-600 mr-2">Quand:</span>
                    <span class="text-sm text-gray-500">${date.toLocaleDateString('fr-FR')} ${date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</span>
                </div>
            </div>
        `;
    }).join('');

    // Combine header and items
    return `
        <div class="bg-white shadow-xl rounded-xl divide-y divide-gray-100">
            <!-- Header for desktop view -->
            <div class="hidden md:grid md:grid-cols-3 gap-4 p-4 text-sm font-semibold text-gray-600 bg-gray-50 rounded-t-xl">
                <div class="col-span-1">Rapporté Par</div>
                <div class="col-span-1 text-right">Quantité Réalisée</div>
                <div class="col-span-1 text-right">Date & Heure</div>
            </div>
            <!-- History Items -->
            ${historyItems}
        </div>
    `;
}

function buildModalStructure(taskName, contentHtml, modalId) {
    return `
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-4xl max-h-[80vh] overflow-y-auto transform scale-100 transition-transform duration-300">
            <div class="flex items-center justify-between mb-6 border-b pb-3">
                <h3 class="text-2xl font-bold text-gray-800">Historique d'Avancement</h3>
                <button onclick="closeProgressHistoryModal('${modalId}')" class="text-gray-400 hover:text-gray-600 transition duration-150 p-2 rounded-full hover:bg-gray-100">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-lg text-gray-700">Tâche: <span class="font-semibold text-indigo-600">${taskName}</span></p>
            </div>
            
            <div class="space-y-6">
                ${contentHtml}
            </div>
        </div>
    `;
}

function buildErrorStructure(taskName, errorMessage) {
    return `
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-4xl max-h-[80vh] overflow-y-auto transform scale-100 transition-transform duration-300">
            <div class="flex items-center justify-between mb-6 border-b pb-3">
                <h3 class="text-2xl font-bold text-gray-800">Historique d'Avancement</h3>
                <button onclick="closeProgressHistoryModal()" class="text-gray-400 hover:text-gray-600 transition duration-150 p-2 rounded-full hover:bg-gray-100">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6 bg-red-50 border-l-4 border-red-400 text-red-700 rounded-lg">
                <p class="font-medium">Erreur lors du chargement de l'historique de la tâche "${taskName}":</p>
                <p class="mt-2 text-sm">${errorMessage}</p>
            </div>
        </div>
    `;
}

/**
 * Closes the progress history modal.
 */
function closeProgressHistoryModal() {
    const modal = document.getElementById('history-modal');
    if (modal) {
        // Simple fade out animation before removal
        modal.style.opacity = '0';
        setTimeout(() => modal.remove(), 300);
    }
}

function showNotification(message, type = 'success') {
    const container = document.getElementById('notification-container');
    if (!container) {
        // Create container if it doesn't exist (assuming this is a typical toast setup)
        const newContainer = document.createElement('div');
        newContainer.id = 'notification-container';
        newContainer.className = 'fixed bottom-4 right-4 z-[100] space-y-3';
        document.body.appendChild(newContainer);
        return showNotification(message, type); // Retry once
    }

    let { bgColor, iconHtml } = {
        'success': { bgColor: 'bg-green-600', iconHtml: '<i class="fas fa-check-circle mr-2"></i>' },
        'error': { bgColor: 'bg-red-600', iconHtml: '<i class="fas fa-exclamation-triangle mr-2"></i>' },
        'info': { bgColor: 'bg-blue-600', iconHtml: '<i class="fas fa-info-circle mr-2"></i>' }
    }[type];

    const toast = document.createElement('div');
    toast.className = `p-4 ${bgColor} text-white rounded-lg shadow-xl flex items-center transition-all duration-300 transform translate-x-0 opacity-0 min-w-[250px]`;
    toast.innerHTML = `${iconHtml}<span>${message}</span>`;

    container.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.replace('opacity-0', 'opacity-100');
        toast.style.transform = 'translateX(0)';
    }, 10);

    // Animate out and remove after 4 seconds
    setTimeout(() => {
        toast.classList.replace('opacity-100', 'opacity-0');
        toast.style.transform = 'translateX(100%)';
        
        // Remove from DOM after transition
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// --- Task Filtering and Sorting (Unchanged logic, maintained for completeness) ---
function filterTasks() {
    const searchTerm = document.getElementById('search-task').value.toLowerCase();
    const statusFilter = document.getElementById('filter-status').value;
    const unitFilter = document.getElementById('filter-unit').value;
    const periodStart = document.getElementById('filter-period-start').value;
    const periodEnd = document.getElementById('filter-period-end').value;
    const sortBy = document.getElementById('sort-by').value;
    
    const rows = Array.from(document.querySelectorAll('.task-row'));
    let visibleCount = 0;
    
    const visibleRows = rows.filter(row => {
        const taskName = row.dataset.taskName.toLowerCase();
        const status = row.dataset.status;
        const unit = row.dataset.unit;
        const createdDate = new Date(row.dataset.created);
        
        let showRow = true;
        
        if (searchTerm && !taskName.includes(searchTerm)) showRow = false;
        if (statusFilter !== 'all' && status !== statusFilter) showRow = false;
        if (unitFilter !== 'all' && unit !== unitFilter) showRow = false;
        
        // Date range filtering
        if (periodStart) {
            const startDate = new Date(periodStart);
            if (createdDate < startDate) showRow = false;
        }
        if (periodEnd) {
            const endDate = new Date(periodEnd);
            endDate.setHours(23, 59, 59, 999); // Include the entire end date
            if (createdDate > endDate) showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
        if (showRow) visibleCount++;
        return showRow;
    });
    
    sortRows(visibleRows, sortBy);
    
    document.getElementById('task-count').textContent = visibleCount;
}

function sortRows(rows, sortBy) {
    const tbody = document.getElementById('tasks-table-body');
    if (!tbody) return;

    rows.sort((a, b) => {
        switch (sortBy) {
            case 'name':
                return a.dataset.taskName.localeCompare(b.dataset.taskName);
            case 'created':
                return new Date(a.dataset.created) - new Date(b.dataset.created);
            case 'planned':
                return parseFloat(b.dataset.planned) - parseFloat(a.dataset.planned);
            case 'progress':
                return parseFloat(b.dataset.progress) - parseFloat(a.dataset.progress);
            default:
                return 0;
        }
    });
    
    // Use a DocumentFragment for efficient DOM reordering
    const fragment = document.createDocumentFragment();
    rows.forEach(row => fragment.appendChild(row));
    tbody.appendChild(fragment);
}

function clearFilters() {
    document.getElementById('search-task').value = '';
    document.getElementById('filter-status').value = 'all';
    document.getElementById('filter-unit').value = 'all';
    document.getElementById('filter-period-start').value = '';
    document.getElementById('filter-period-end').value = '';
    document.getElementById('sort-by').value = 'name';
    filterTasks();
}

// --- Progress Modal Functions ---
function openProgressModal(button) {
    const taskId = button.dataset.taskId;
    const taskName = button.dataset.taskName;
    currentTaskPlanned = parseFloat(button.dataset.planned);
    currentTaskAchieved = parseFloat(button.dataset.achieved);

    const maxQuantity = Math.max(0, currentTaskPlanned - currentTaskAchieved).toFixed(2);

    document.getElementById('modal-task-name').textContent = `Tâche: ${taskName}`;
    document.getElementById('modal-task-id').value = taskId;
    document.getElementById('progress-form').dataset.taskId = taskId;
    document.getElementById('daily_quantity').value = '';
    document.getElementById('max-quantity').textContent = maxQuantity;
    document.getElementById('daily_quantity').setAttribute('max', maxQuantity);
    document.getElementById('error-message').classList.add('hidden');

    const modal = document.getElementById('progress-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeProgressModal() {
    const modal = document.getElementById('progress-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// --- Add Task Modal Functions ---
function openAddTaskModal(projectId) {
    document.getElementById('add-task-project-id').value = projectId;
    document.getElementById('add-task-form').reset();
    document.getElementById('add-error-message').classList.add('hidden');
    
    const modal = document.getElementById('add-task-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeAddTaskModal() {
    const modal = document.getElementById('add-task-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// --- DOMContentLoaded: Event Listeners ---
document.addEventListener('DOMContentLoaded', () => {
    // Filter event listeners
    const filterInputs = ['filter-status', 'filter-unit', 'filter-period-start', 'filter-period-end', 'sort-by'];
    filterInputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', filterTasks);
    });
    // Search input listener (on keyup for real-time feedback)
    const searchInput = document.getElementById('search-task');
    if (searchInput) searchInput.addEventListener('keyup', filterTasks);

    // Progress form submission
    const progressForm = document.getElementById('progress-form');
    if (progressForm) {
        progressForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const taskId = progressForm.dataset.taskId;
            const dailyQuantity = parseFloat(document.getElementById('daily_quantity').value);
            const maxQuantity = parseFloat(document.getElementById('daily_quantity').getAttribute('max'));
            const errorEl = document.getElementById('error-message');

            if (dailyQuantity > maxQuantity) {
                errorEl.textContent = `La quantité ne peut pas dépasser ${maxQuantity}.`;
                errorEl.classList.remove('hidden');
                return;
            }
            errorEl.classList.add('hidden');

            try {
                const data = await apiFetch(`/tasks/${taskId}/progress`, {
                    method: 'PUT',
                    body: { daily_quantity: dailyQuantity }
                });

                // Update the task row in the UI
                const taskRow = document.getElementById(`task-row-${taskId}`);
                if (taskRow) {
                    const updatedTask = data.task;
                    const progressPercentage = updatedTask.planned > 0 ? (updatedTask.total_achieved / updatedTask.planned * 100) : 0;
                    
                    // Update data attributes for filtering/sorting
                    taskRow.dataset.achieved = updatedTask.total_achieved;
                    taskRow.dataset.progress = progressPercentage;
                    taskRow.dataset.status = updatedTask.finished_date ? 'completed' : 'in-progress';

                    // Update visible values
                    taskRow.querySelector('.task-achieved-total').textContent = parseFloat(updatedTask.total_achieved).toLocaleString('fr-FR', { minimumFractionDigits: 2 });
                    taskRow.querySelector('.task-daily-progress').textContent = parseFloat(updatedTask.accomplished_quantity).toLocaleString('fr-FR', { minimumFractionDigits: 2 });
                    
                    const progressCell = taskRow.querySelector('.task-progress-cell');
                    progressCell.querySelector('.bg-blue-600').style.width = `${Math.min(100, progressPercentage)}%`;
                    progressCell.querySelector('span').textContent = `${progressPercentage.toFixed(1)}%`;
                    
                    const finishedDateCell = taskRow.querySelector('.task-finished-date-cell');
                    if (updatedTask.finished_date) {
                        finishedDateCell.innerHTML = `<span class="text-green-600 font-medium">${new Date(updatedTask.finished_date).toLocaleDateString('fr-FR')}</span>`;
                    } else {
                        finishedDateCell.innerHTML = `<span class="text-orange-500">En cours</span>`;
                    }
                }
                
                closeProgressModal();
                showNotification('Avancement enregistré avec succès!', 'success');
            } catch (error) {
                showNotification(`Erreur: ${error.message}`, 'error');
            }
        });
    }

    // Add task form submission
    const addTaskForm = document.getElementById('add-task-form');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(addTaskForm);
            const taskData = {
                project_id: formData.get('project_id'),
                task: formData.get('task_name'),
                planned: formData.get('planned_quantity'),
                unit_measure: formData.get('unit_measure')
            };

            try {
                await apiFetch('{{ route("tasks.store") }}', {
                    method: 'POST',
                    body: taskData
                });
                
                showNotification('Tâche ajoutée avec succès! La page va se recharger.', 'success');
                setTimeout(() => window.location.reload(), 1500);

            } catch (error) {
                const errorEl = document.getElementById('add-error-message');
                errorEl.textContent = error.message || 'Une erreur est survenue.';
                errorEl.classList.remove('hidden');
                showNotification(`Erreur: ${error.message}`, 'error');
            }
        });
    }
});
</script>
@endpush