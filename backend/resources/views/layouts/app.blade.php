<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Application') | Progress Tracker</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="{{ asset('js/tailwind-cdn.js') }}"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/> 
</head>
@stack('scripts')
<body class="bg-gray-100 font-sans antialiased">

    <div id="app" class="flex h-screen">

        <aside class="w-64 bg-gray-800 text-white shadow-xl flex-shrink-0">
            <div class="p-6 text-center text-2xl font-semibold border-b border-gray-700">
                Tracker
            </div>
            
            <nav class="mt-6">
                @if(Auth::check())
                    {{-- Director Dashboard --}}
                    @if(Auth::user()->role === 'directeur')
                        <a href="{{ route('dashboard.director') }}" 
                           class="block py-2.5 px-6 transition duration-200 hover:bg-gray-700 @if(request()->routeIs('dashboard.director')) bg-gray-900 @endif">
                            <i class="fas fa-tachometer-alt mr-2"></i> Tableau de bord (Dir.)
                        </a>
                    @endif
                    
                    {{-- Agent/Unit View --}}
                    <a href="{{ route('units.index') }}" 
                       class="block py-2.5 px-6 transition duration-200 hover:bg-gray-700 @if(request()->routeIs('units.index') || request()->routeIs('units.projects')) bg-gray-900 @endif">
                        <i class="fas fa-building mr-2"></i> Unités & Projets
                    </a>
                    
                    {{-- General Dashboard (Fallback/Home) --}}
                    <a href="{{ route('dashboard') }}" 
                       class="block py-2.5 px-6 transition duration-200 hover:bg-gray-700 @if(request()->routeIs('dashboard')) bg-gray-900 @endif">
                        <i class="fas fa-list-alt mr-2"></i> Vue d'ensemble
                    </a>
                @endif
            </nav>
        </aside>

        <main class="flex-1 flex flex-col overflow-hidden">
            
            <header class="bg-white shadow-md h-16 flex items-center justify-between px-6 flex-shrink-0">
                <div class="text-xl font-medium text-gray-700">
                    @yield('title')
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        {{ Auth::user()->username }} ({{ ucfirst(Auth::user()->role) }})
                    </span>
                    
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-red-500 hover:text-red-700 transition duration-200 text-sm font-medium">
                            Déconnexion
                        </button>
                    </form>
                </div>
            </header>

            <div class="flex-1 overflow-x-hidden overflow-y-auto p-6">
                @if (session('status'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ session('status') }}</p>
                    </div>
                @endif
                
                @yield('content')
            </div>

        </main>
    </div>

    
    @stack('scripts')
</body>
</html>