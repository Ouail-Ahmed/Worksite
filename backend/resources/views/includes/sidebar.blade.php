@php
    // Get the current authenticated user
    $user = Auth::user();
@endphp

<aside class="w-64 bg-gray-800 text-white shadow-2xl flex-shrink-0 h-full">
    <div class="p-6 text-center text-2xl font-semibold border-b border-gray-700 bg-gray-900">
        Progress Tracker
    </div>
    
    <div class="p-4 text-center border-b border-gray-700">
        <p class="text-sm font-medium">{{ $user->username }}</p>
        <p class="text-xs text-blue-300">Rôle: {{ ucfirst($user->role) }}</p>
    </div>

    <nav class="mt-4 space-y-2">
        
        {{-- General Dashboard/Home Link (Fallback) --}}
        <a href="{{ route('dashboard') }}" 
           class="flex items-center py-2.5 px-6 transition duration-200 hover:bg-gray-700 
           @if(request()->routeIs('dashboard')) bg-gray-900 font-bold @endif">
            <i class="fas fa-home w-5"></i>
            <span class="ml-3">Accueil</span>
        </a>

        {{-- Role-Specific Links --}}
        
        @if($user->role === 'directeur')
            {{-- Director Dashboard --}}
            <a href="{{ route('dashboard.director') }}" 
               class="flex items-center py-2.5 px-6 transition duration-200 hover:bg-gray-700 
               @if(request()->routeIs('dashboard.director')) bg-gray-900 font-bold @endif">
                <i class="fas fa-chart-line w-5"></i>
                <span class="ml-3">Synthèse Directeur</span>
            </a>
            
            {{-- Management Link (If implemented) --}}
            <a href="#" 
               class="flex items-center py-2.5 px-6 transition duration-200 hover:bg-gray-700">
                <i class="fas fa-users-cog w-5"></i>
                <span class="ml-3">Gestion des Utilisateurs</span>
            </a>
        @endif
        
        {{-- Unit and Project Navigation (Primary for Agents) --}}
        <a href="{{ route('units.index') }}" 
           class="flex items-center py-2.5 px-6 transition duration-200 hover:bg-gray-700 
           @if(request()->routeIs('units.index') || request()->routeIs('units.projects') || request()->routeIs('projects.show')) bg-gray-900 font-bold @endif">
            <i class="fas fa-cubes w-5"></i>
            <span class="ml-3">Unités & Projets</span>
        </a>
        
    </nav>

    <div class="mt-8 pt-4 border-t border-gray-700 mx-4">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center w-full py-2.5 px-2 text-red-400 hover:bg-gray-700 rounded transition duration-200">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span class="ml-3">Déconnexion</span>
            </button>
        </form>
    </div>

</aside>