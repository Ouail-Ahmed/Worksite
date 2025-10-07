@extends('layouts.app')

@section('title', 'Tableau de Bord Agent')

@section('content')
    <div class="container mx-auto">
        <h1 class="text-3xl font-extrabold text-gray-800 mb-6 border-b pb-2">
            Vue d'ensemble des Unités
        </h1>

        <p class="text-gray-600 mb-8">
            Bienvenue, {{ $user->username }}. Sélectionnez une unité pour consulter et mettre à jour l'avancement de ses projets.
        </p>

        @if ($units->isEmpty())
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p class="font-bold">Aucune Unité Trouvée</p>
                <p>Il n'y a actuellement aucune unité définie dans le système.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($units as $unit)
                    <a href="{{ route('units.projects', $unit) }}" class="block">
                        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-6 border-t-4 border-blue-500 flex flex-col h-full">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-2">
                                {{ $unit->name }}
                            </h2>
                            <p class="text-sm text-gray-500 mb-4 flex-grow">
                                {{-- Assuming the Unit model has a project count from UnitController@index --}}
                                Projets actifs : <span class="font-bold text-blue-600">{{ $unit->projects_count }}</span>
                            </p>
                            
                            {{-- Placeholder for a small progress indicator if you later calculate unit-level progress --}}
                            <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                <span class="text-sm text-blue-500 font-medium">
                                    Voir les projets <i class="fas fa-arrow-right ml-1"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection