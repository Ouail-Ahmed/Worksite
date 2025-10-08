@extends('layouts.app')

@section('title', 'Tableau de Bord Directeur')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-8 border-b pb-3">
            Synthèse Globale de l'Activité
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500 hover:shadow-xl transition duration-300">
                <p class="text-sm font-medium text-gray-500">Unités Opérationnelles</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">
                    {{ number_format($stats['total_units'] ?? 0) }}
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-gray-500 hover:shadow-xl transition duration-300">
                <p class="text-sm font-medium text-gray-500">Projets Totaux</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">
                    {{ number_format($stats['total_projects'] ?? 0) }}
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500 hover:shadow-xl transition duration-300">
                <p class="text-sm font-medium text-gray-500">Utilisateurs Enregistrés</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">
                    {{ number_format($stats['total_users'] ?? 0) }}
                </p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                Avancement Global (Tous Projets Confondus)
            </h2>
            
            @php
                // Ensure $globalProgress is between 0 and 100 and cast to integer
                $progress = round(max(0, min(100, $globalProgress ?? 0)));
                $barColor = $progress < 50 ? 'bg-red-500' : ($progress < 85 ? 'bg-orange-500' : 'bg-green-600');
            @endphp

            <div class="h-4 bg-gray-200 rounded-full mb-4 overflow-hidden">
                <div 
                    class="h-4 {{ $barColor }} rounded-full transition-all duration-500" 
                    style="width: {{ $progress }}%;"
                    role="progressbar"
                    aria-valuenow="{{ $progress }}"
                    aria-valuemin="0"
                    aria-valuemax="100"
                ></div>
            </div>
            <p class="text-right text-xl font-bold text-gray-700">{{ $progress }}% Achieved</p>

            <p class="text-gray-500 mt-4 text-sm">
                *Ceci représente le rapport entre la quantité totale réalisée et la quantité totale planifiée pour l'ensemble des tâches de tous les projets.
            </p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Projets par Unité</h3>
                <canvas id="projectsBarChart"></canvas>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Répartition de l'Avancement</h3>
                <canvas id="progressPieChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                Performance Détaillée par Unité
            </h2>

            <div class="overflow-x-auto rounded-lg border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unité
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Projets
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Avancement Total
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        
                        @forelse ($unitsData as $unit)
                            @php
                                // Ensure unit progress is safe and cast to integer
                                $unitProgress = round(max(0, min(100, $unit->overall_progress_percentage ?? 0)));
                                $unitBarColor = $unitProgress < 50 ? 'bg-red-500' : ($unitProgress < 85 ? 'bg-orange-500' : 'bg-green-600');
                            @endphp
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $unit->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $unit->total_projects_count ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="w-24 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="{{ $unitBarColor }} text-xs font-bold text-white text-center p-0.5 leading-none rounded-full" style="width: {{ $unitProgress }}%"> {{ $unitProgress }}% </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    {{-- Uses the named route 'units.projects' defined in web.php --}}
                                    <a href="{{ route('units.projects', $unit) }}" class="text-blue-600 hover:text-blue-800 font-semibold">Détails</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 italic">
                                    Aucune unité opérationnelle trouvée ou à afficher.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="mt-6 text-sm text-gray-500">
                Les liens "Détails" mènent à la liste des projets pour chaque unité.
            </p>
        </div>

    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Bar Chart - Projects per Unit
        const barCtx = document.getElementById('projectsBarChart').getContext('2d');
        const projectsBarChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($unitsData->pluck('name')) !!},
                datasets: [{
                    label: 'Nombre de Projets',
                    data: {!! json_encode($unitsData->pluck('total_projects_count')) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Pie Chart - Progress Distribution
        const pieCtx = document.getElementById('progressPieChart').getContext('2d');
        const progressPieChart = new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($unitsData->pluck('name')) !!},
                datasets: [{
                    label: 'Avancement (%)',
                    data: {!! json_encode($unitsData->pluck('overall_progress_percentage')) !!},
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ],
                    borderColor: 'white',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.parsed || 0;
                                return context.label + ': ' + value.toFixed(2) + '%';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush