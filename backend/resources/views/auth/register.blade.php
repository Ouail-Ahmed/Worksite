<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Application</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white shadow-2xl rounded-xl p-8 w-full max-w-lg">
        <h2 class="text-3xl font-extrabold text-center mb-8 text-gray-800">
            Inscription 📝
        </h2>

        <form action="{{ route('register') }}" method="POST">
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
                    class="border border-gray-300 p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('username') border-red-500 @enderror"
                >
                @error('username')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-700">
                    Mot de passe (Min. 8 caractères)
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    required 
                    class="border border-gray-300 p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                >
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-700">
                    Confirmer le mot de passe
                </label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation"
                    required 
                    class="border border-gray-300 p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <div class="mb-6">
                <label for="role" class="block mb-2 text-sm font-medium text-gray-700">
                    Rôle
                </label>
                <select 
                    name="role" 
                    id="role"
                    required 
                    class="border border-gray-300 p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('role') border-red-500 @enderror"
                >
                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>Choisir un rôle</option>
                    <option value="agent" {{ old('role') == 'agent' ? 'selected' : '' }}>Agent</option>
                    <option value="directeur" {{ old('role') == 'directeur' ? 'selected' : '' }}>Directeur</option>
                </select>
                @error('role')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bg-blue-600 text-white font-bold py-3 w-full rounded-lg hover:bg-blue-700 transition duration-200 shadow-md">
                Créer un compte
            </button>
        </form>

        <p class="text-center text-sm mt-6 text-gray-600">
            Vous avez déjà un compte ?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-bold">
                Se connecter
            </a>
        </p>
    </div>

</body>
</html>