<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Application</title>
    <script src="{{ asset('js/tailwind-cdn.js') }}"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white shadow-2xl rounded-xl p-8 w-full max-w-md">
        <h2 class="text-3xl font-extrabold text-center mb-8 text-gray-800">
            Connexion 🔑
        </h2>

        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @error('username')
            <p class="text-red-600 text-sm mb-4 p-3 bg-red-50 border border-red-200 rounded">
                {{ $message }}
            </p>
        @enderror
        @error('password')
            <p class="text-red-600 text-sm mb-4 p-3 bg-red-50 border border-red-200 rounded">
                {{ $message }}
            </p>
        @enderror


        <form action="{{ route('login') }}" method="POST">
            @csrf 

            <div class="mb-4">
                <label for="username" class="block mb-2 text-sm font-medium text-gray-700">
                    Nom d'utilisateur
                </label>
                <input 
                    type="text" 
                    name="username" 
                    id="username"
                    value="{{ old('username') }}" 
                    required 
                    autofocus
                    class="border border-gray-300 p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('username') border-red-500 @enderror"
                >
            </div>

            <div class="mb-6">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-700">
                    Mot de passe
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    required 
                    class="border border-gray-300 p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                >
            </div>

            <button type="submit" class="bg-blue-600 text-white font-bold py-3 w-full rounded-lg hover:bg-blue-700 transition duration-200 shadow-md">
                Se connecter
            </button>
        </form>

        <p class="text-center text-sm mt-6 text-gray-600">
            Vous n'avez pas de compte ?
            <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-bold">
                S'inscrire
            </a>
        </p>
    </div>

</body>
</html>