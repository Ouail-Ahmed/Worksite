<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Welcome') | Application</title>
    <script src="{{ asset('js/tailwind-cdn.js') }}"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        
        <div class="mb-4">
            <h1 class="text-3xl font-bold text-gray-800">
                Application Progress
            </h1>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-xl overflow-hidden sm:rounded-lg">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>