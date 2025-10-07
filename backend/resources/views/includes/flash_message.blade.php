@if (session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md" role="alert">
        <div class="flex">
            <div class="py-1"><i class="fas fa-check-circle mr-3"></i></div>
            <div>
                <p class="font-bold">Succès</p>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert">
        <div class="flex">
            <div class="py-1"><i class="fas fa-exclamation-triangle mr-3"></i></div>
            <div>
                <p class="font-bold">Erreur</p>
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session('status'))
    {{-- 'status' is often used for general messages, like after logout --}}
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 rounded-md" role="alert">
        <div class="flex">
            <div class="py-1"><i class="fas fa-info-circle mr-3"></i></div>
            <div>
                <p class="font-bold">Information</p>
                <p class="text-sm">{{ session('status') }}</p>
            </div>
        </div>
    </div>
@endif

{{-- Optional: Handle validation errors that were manually flashed --}}
@if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert">
        <div class="flex">
            <div class="py-1"><i class="fas fa-times-circle mr-3"></i></div>
            <div>
                <p class="font-bold">Erreur de validation</p>
                <ul class="mt-1 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif