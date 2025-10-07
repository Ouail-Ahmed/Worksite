@extends('layouts.app')

{{-- Use the user's role in the title for clarity --}}
@section('title', 'Liste des Unités Opérationnelles')

@section('content')
    <div class="container mx-auto px-4">
        {{-- Header and Add Unit Button --}}
        <div class="flex items-center justify-between mb-6 border-b pb-2">
            <h1 class="text-3xl font-extrabold text-gray-800">
                Unités de l'Organisation
            </h1>
            
            {{-- 1. Authorization Check for 'Directeur' --}}
            @if (Auth::user()->role === 'directeur')
                <button 
                    onclick="document.getElementById('add-unit-modal').classList.remove('hidden')"
                    class="flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 transition duration-150 text-sm font-medium"
                >
                    <i class="fas fa-building mr-2"></i> Ajouter Une Unité
                </button>
            @endif
        </div>

        @if ($units->isEmpty())
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p class="font-bold">Aucune Unité Trouvée</p>
                @if (Auth::user()->role === 'directeur')
                <p>Utilisez le bouton "Ajouter Une Unité" pour créer la première unité.</p>
                @else
                <p>Veuillez contacter l'administrateur pour ajouter de nouvelles unités.</p>
                @endif
            </div>
        @else
            {{-- Existing Units Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($units as $unit)
                    <a href="{{ route('units.projects', $unit) }}" class="block">
                        <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition duration-300 transform hover:scale-[1.01] p-6 border-b-4 border-blue-500 flex flex-col h-full">
                            
                            <h2 class="text-2xl font-bold text-blue-600 mb-2 truncate">
                                {{ $unit->name }}
                            </h2>
                            
                            <p class="text-sm text-gray-500 mb-4 flex-grow">
                                **{{ $unit->projects_count }}** Projets associés
                            </p>
                            
                            <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                                <span class="text-blue-500 font-medium text-sm hover:underline">
                                    Consulter les projets <i class="fas fa-arrow-right ml-1"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- 3. Add Unit Modal (Hidden by default) --}}
    <div id="add-unit-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden flex items-center justify-center z-50 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-lg mx-4">
            {{-- Modal Header --}}
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-xl font-bold text-gray-800">Ajouter une nouvelle unité</h3>
                <button 
                    onclick="document.getElementById('add-unit-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            {{-- Unit Creation Form --}}
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom de l'Unité</label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        required 
                        class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ex: Direction Technique"
                    >
                    {{-- Optional: Add error message display here if using AJAX/Livewire --}}
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Action Buttons --}}
                <div class="flex justify-end space-x-3 mt-6">
                    <button 
                        type="button" 
                        onclick="document.getElementById('add-unit-modal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-150"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150 font-medium"
                    >
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
