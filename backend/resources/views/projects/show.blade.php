@extends('layouts.app')

@section('title', 'Détails du Projet : ' . $project->name)

@push('styles')
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container mx-auto">
        <div class="flex items-center justify-between mb-6 border-b pb-3">
            <h1 class="text-3xl font-extrabold text-gray-800">
                Projet : <span class="text-blue-600">{{ $project->name }}</span>
            </h1>
            <a href="{{ route('units.projects', $project->unit) }}" class="text-sm font-medium text-gray-500 hover:text-blue-600">
                <i class="fas fa-arrow-left mr-1"></i> Retour aux projets de l'unité
            </a>
        </div>

<div class="bg-white p-6 rounded-xl shadow-lg mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
    <div class="flex-1 space-y-2">
    <p class="text-sm text-gray-600"><span class="font-semibold">Unité :</span> {{ $project->unit->name }}</p>
    <p class="text-sm text-gray-600"><span class="font-semibold">Période :</span> {{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($project->finish_date)->format('d/m/Y') }}</p>
    <p class="text-sm text-gray-600"><span class="font-semibold">Type/Section :</span> {{ $project->type }} / {{ $project->section }}</p>
    </div>
    
    <div class="flex-shrink-0 text-center border-l md:pl-6">
    <p class="text-xl font-semibold mb-2 text-gray-700">Avancement Global</p>
    <div class="relative w-32 h-32 mx-auto" x-data="{ progress: {{ $progress }} }">
        <div class="w-full h-full rounded-full bg-gray-200 flex items-center justify-center text-3xl font-bold text-green-600">
        {{ $progress }}%
        </div>
    </div>
    <p class="text-sm text-gray-500 mt-2">Planifié Total: <span class="font-bold">{{ number_format($totalPlanned, 2) }}</span></p>
    <p class="text-sm text-gray-500">Réalisé Total: <span class="font-bold">{{ number_format($totalAchieved, 2) }}</span></p>
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

{{-- Filter Section --}}
<div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex items-center space-x-2">
            <label for="search-task" class="text-sm font-medium text-gray-700">Rechercher:</label>
            <input type="text" id="search-task" placeholder="Nom de la tâche..." 
                class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
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

<div class="overflow-x-auto bg-white rounded-xl shadow-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tâche</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unité</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Planifié</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Réalisé</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progrès J.</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date Créée</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date Terminée</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200" id="tasks-table-body">
            @forelse ($project->tasks as $task)
                <tr id="task-row-{{ $task->id }}" class="task-row" 
                    data-task-name="{{ strtolower($task->task) }}"
                    data-unit="{{ $task->unit_measure }}"
                    data-status="{{ $task->finished_date ? 'completed' : ($task->total_achieved > 0 ? 'in-progress' : 'not-started') }}"
                    data-created="{{ $task->created_at }}"
                    data-planned="{{ $task->planned }}"
                    data-progress="{{ $task->planned > 0 ? ($task->total_achieved / $task->planned * 100) : 0 }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $task->task }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">{{ $task->unit_measure }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700" data-planned="{{ $task->planned }}">{{ number_format($task->planned, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-600 font-bold task-achieved-total" data-total-achieved="{{ $task->total_achieved }}">{{ number_format($task->total_achieved, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-blue-600 task-daily-progress" data-daily-progress="{{ $task->accomplished_quantity }}">{{ number_format($task->accomplished_quantity, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">{{ \Carbon\Carbon::parse($task->created_at)->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                        @if($task->finished_date)
                            <span class="text-green-600 font-medium">{{ \Carbon\Carbon::parse($task->finished_date)->format('d/m/Y') }}</span>
                        @else
                            <span class="text-orange-500">En cours</span>
                        @endif
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        @if (Auth::check() && Auth::user()->role === 'agent')
                        <button 
                            onclick="openProgressModal(this)"
                            data-task-id="{{ $task->id }}"
                            data-task-name="{{ addslashes($task->task) }}"
                            data-planned="{{ $task->planned }}"
                            data-achieved="{{ $task->total_achieved }}"
                            class="text-indigo-600 hover:text-indigo-900 transition duration-150 px-3 py-1 border border-indigo-200 rounded-md text-xs hover:bg-indigo-50"
                        >
                            Reporter Avancement
                        </button>
                        @else
                            <span class="text-gray-400 text-xs">Lecture Seule</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr id="no-tasks-row">
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Aucune tâche n'a été définie pour ce projet.</td>
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

    {{-- MODAL: ADD TASK --}}
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
    // --- Global variables and functions ---
    let currentTaskPlanned = 0;
    let currentTaskAchieved = 0;
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    // Filter functions
    function filterTasks() {
        const searchTerm = document.getElementById('search-task').value.toLowerCase();
        const statusFilter = document.getElementById('filter-status').value;
        const unitFilter = document.getElementById('filter-unit').value;
        const sortBy = document.getElementById('sort-by').value;
        
        const rows = Array.from(document.querySelectorAll('.task-row'));
        let visibleCount = 0;
        
        // Filter rows
        rows.forEach(row => {
            const taskName = row.dataset.taskName;
            const status = row.dataset.status;
            const unit = row.dataset.unit;
            
            let showRow = true;
            
            // Search filter
            if (searchTerm && !taskName.includes(searchTerm)) {
                showRow = false;
            }
            
            // Status filter
            if (statusFilter !== 'all' && status !== statusFilter) {
                showRow = false;
            }
            
            // Unit filter
            if (unitFilter !== 'all' && unit !== unitFilter) {
                showRow = false;
            }
            
            if (showRow) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Sort visible rows
        const visibleRows = rows.filter(row => row.style.display !== 'none');
        sortRows(visibleRows, sortBy);
        
        // Update count
        document.getElementById('task-count').textContent = visibleCount;
    }
    
    function sortRows(rows, sortBy) {
        const tbody = document.getElementById('tasks-table-body');
        
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
        
        // Reorder in DOM
        rows.forEach(row => tbody.appendChild(row));
    }
    
    function clearFilters() {
        document.getElementById('search-task').value = '';
        document.getElementById('filter-status').value = 'all';
        document.getElementById('filter-unit').value = 'all';
        document.getElementById('sort-by').value = 'name';
        filterTasks();
    }

    function openProgressModal(buttonEl) {
        const taskId = buttonEl.getAttribute('data-task-id');
        const taskName = buttonEl.getAttribute('data-task-name');
        const planned = parseFloat(buttonEl.getAttribute('data-planned'));
        const achieved = parseFloat(buttonEl.getAttribute('data-achieved'));
        
        currentTaskPlanned = planned;
        currentTaskAchieved = achieved;
        const remaining = Math.max(planned - achieved, 0);
        
        const form = document.getElementById('progress-form');
        form.action = `/tasks/${taskId}/report-progress`;

        document.getElementById('modal-task-id').value = taskId;
        document.getElementById('modal-task-name').textContent = `Tâche: ${taskName} (Planifié: ${planned.toLocaleString()}, Déjà Réalisé: ${achieved.toLocaleString()})`;
        
        document.getElementById('max-quantity').textContent = remaining.toLocaleString(undefined, { minimumFractionDigits: 2 });
        document.getElementById('daily_quantity').setAttribute('max', remaining);
        document.getElementById('daily_quantity').value = '';
        
        document.getElementById('progress-modal').classList.remove('hidden');
        document.getElementById('progress-modal').classList.add('flex');
        document.getElementById('daily_quantity').focus();
    }

    function closeProgressModal() {
        document.getElementById('progress-modal').classList.add('hidden');
        document.getElementById('progress-modal').classList.remove('flex');
        document.getElementById('error-message').classList.add('hidden');
        document.getElementById('progress-form').reset();
    }

    function openAddTaskModal(projectId) {
        document.getElementById('add-task-project-id').value = projectId;
        document.getElementById('add-task-form').action = `/projects/${projectId}/tasks`;

        document.getElementById('add-task-modal').classList.remove('hidden');
        document.getElementById('add-task-modal').classList.add('flex');
        document.getElementById('task_name').focus();
    }

    function closeAddTaskModal() {
        document.getElementById('add-task-modal').classList.add('hidden');
        document.getElementById('add-task-modal').classList.remove('flex');
        document.getElementById('add-error-message').classList.add('hidden');
        document.getElementById('add-task-form').reset();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners for filters
        document.getElementById('search-task').addEventListener('input', filterTasks);
        document.getElementById('filter-status').addEventListener('change', filterTasks);
        document.getElementById('filter-unit').addEventListener('change', filterTasks);
        document.getElementById('sort-by').addEventListener('change', filterTasks);

        // Form Submission for New Task
        document.getElementById('add-task-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const errorMessage = document.getElementById('add-error-message');
            const createBtn = document.getElementById('create-task-btn');
            
            errorMessage.classList.add('hidden');
            createBtn.disabled = true;
            createBtn.textContent = 'Création...';

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        task: data.task_name,
                        planned: data.planned_quantity,
                        unit_measure: data.unit_measure,
                        project_id: data.project_id
                    })
                });

                const responseData = await response.json();

                if (response.ok) {
                    const task = responseData.task;
                    const tableBody = document.getElementById('tasks-table-body');
                    const noTasksRow = document.getElementById('no-tasks-row');
                    
                    if (noTasksRow) {
                        noTasksRow.remove();
                    }

                    const newRow = document.createElement('tr');
                    newRow.id = `task-row-${task.id}`;
                    newRow.className = 'task-row';
                    newRow.dataset.taskName = task.task.toLowerCase();
                    newRow.dataset.unit = task.unit_measure;
                    newRow.dataset.status = 'not-started';
                    newRow.dataset.created = task.created_at;
                    newRow.dataset.planned = task.planned;
                    newRow.dataset.progress = '0';
                    
                    newRow.innerHTML = `
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">${task.task}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">${task.unit_measure}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700" data-planned="${task.planned}">${(task.planned).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-600 font-bold task-achieved-total" data-total-achieved="0.00">0.00</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-blue-600 task-daily-progress" data-daily-progress="0.00">0.00</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">${new Date(task.created_at).toLocaleDateString('fr-FR')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            <span class="text-orange-500">En cours</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button 
                                onclick="openProgressModal(this)"
                                data-task-id="${task.id}"
                                data-task-name="${task.task}"
                                data-planned="${task.planned}"
                                data-achieved="0.00"
                                class="text-indigo-600 hover:text-indigo-900 transition duration-150 p-2 border rounded-lg text-xs"
                            >
                                Reporter Avancement
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(newRow);

                    // Update filter options
                    const unitFilter = document.getElementById('filter-unit');
                    if (!Array.from(unitFilter.options).some(option => option.value === task.unit_measure)) {
                        const newOption = document.createElement('option');
                        newOption.value = task.unit_measure;
                        newOption.textContent = task.unit_measure;
                        unitFilter.appendChild(newOption);
                    }

                    closeAddTaskModal();
                    filterTasks(); // Refresh filters
                    alert('Tâche créée avec succès !');
                } else {
                    const errorText = responseData.message || 'Erreur lors de la création de la tâche.';
                    errorMessage.textContent = errorText;
                    errorMessage.classList.remove('hidden');
                    
                    if (responseData.errors) {
                        let errorList = '';
                        for (const key in responseData.errors) {
                            errorList += responseData.errors[key].join(', ') + ' ';
                        }
                        errorMessage.textContent = errorList.trim();
                    }
                }
            } catch (error) {
                console.error('Erreur AJAX:', error);
                errorMessage.textContent = 'Erreur de connexion.';
                errorMessage.classList.remove('hidden');
            } finally {
                createBtn.disabled = false;
                createBtn.textContent = 'Créer Tâche';
            }
        });

        // Progress Form Submission
        document.getElementById('progress-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const taskId = document.getElementById('modal-task-id').value;
            const dailyQuantityInput = document.getElementById('daily_quantity');
            const dailyQuantity = parseFloat(dailyQuantityInput.value);
            const errorMessage = document.getElementById('error-message');
            const saveBtn = document.getElementById('save-progress-btn');
            
            errorMessage.classList.add('hidden');
            saveBtn.disabled = true;
            saveBtn.textContent = 'Enregistrement...';

            const remaining = currentTaskPlanned - currentTaskAchieved;
            if (dailyQuantity > remaining) {
                errorMessage.textContent = `La quantité réalisée ne peut pas dépasser le Reste À Réaliser (${remaining.toFixed(2)})`;
                errorMessage.classList.remove('hidden');
                saveBtn.disabled = false;
                saveBtn.textContent = 'Enregistrer';
                return;
            }
            
            try {
                const response = await fetch(form.action, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        daily_quantity: dailyQuantity
                    })
                });

                const data = await response.json();
                if (response.ok) {
                    const row = document.getElementById(`task-row-${taskId}`);
                    const newTotalAchieved = data.new_achieved_total;
                    const dailyProgress = data.daily_progress;
                    const finishedDate = data.finished_date;
                    
                    const achievedCell = row.querySelector('.task-achieved-total');
                    achievedCell.textContent = newTotalAchieved.toLocaleString(undefined, { minimumFractionDigits: 2 });
                    achievedCell.dataset.totalAchieved = newTotalAchieved;
                    
                    const dailyCell = row.querySelector('.task-daily-progress');
                    dailyCell.textContent = dailyProgress.toLocaleString(undefined, { minimumFractionDigits: 2 });
                    dailyCell.dataset.dailyProgress = dailyProgress;

                    // Update data attributes for filtering
                    const progress = currentTaskPlanned > 0 ? (newTotalAchieved / currentTaskPlanned * 100) : 0;
                    row.dataset.progress = progress;
                    row.dataset.status = finishedDate ? 'completed' : (newTotalAchieved > 0 ? 'in-progress' : 'not-started');

                    const finishedDateCell = row.cells[6];
                    if (finishedDate) {
                        finishedDateCell.innerHTML = `<span class="text-green-600 font-medium">${finishedDate}</span>`;
                    } else {
                        finishedDateCell.innerHTML = `<span class="text-orange-500">En cours</span>`;
                    }

                    closeProgressModal();
                    filterTasks(); // Refresh filters
                    alert('Avancement enregistré avec succès !');
                } else {
                    const errorText = data.message || 'Une erreur est survenue lors de l\'enregistrement.';
                    errorMessage.textContent = errorText;
                    errorMessage.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Erreur AJAX:', error);
                errorMessage.textContent = 'Erreur de connexion. Veuillez vérifier votre réseau.';
                errorMessage.classList.remove('hidden');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Enregistrer';
            }
        });
    });
</script>
@endpush