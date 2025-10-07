<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate; // Added for robust authorization

class UnitController extends Controller
{
    /**
     * Display a listing of all units (Web View).
     * Route: GET /units -> name('units.index')
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Fetch units
        $units = Unit::withCount('projects')
            ->orderBy('name', 'asc')
            ->get();

        return view('units.index', compact('units', 'user'));
    }

    /**
     * Store a newly created unit in the database.
     * Route: POST /units -> name('units.store')
     * * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Authorization: Only 'directeur' can create units.
        if (Auth::user()->role !== 'directeur') {
            // Alternatively, use Gate or Policy for more robust auth
            abort(403, 'Unauthorized action. Only Directors can add new units.');
        }

        // 2. Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
        ], [
            'name.required' => 'Le nom de l\'unité est obligatoire.',
            'name.unique' => 'Une unité avec ce nom existe déjà.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
        ]);

        // 3. Create the Unit
        try {
            Unit::create([
                'name' => $validated['name'],
                // Add any other required fields here (e.g., 'created_by' => Auth::id())
            ]);

            // 4. Redirect with Success Message
            return redirect()->route('units.index')
                ->with('success', 'L\'unité "' . $validated['name'] . '" a été ajoutée avec succès !');
        } catch (\Exception $e) {
            // 5. Handle potential errors (e.g., database connection issues)
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'ajout de l\'unité. Veuillez réessayer.');
        }
    }

    /**
     * API: Fetch a list of all units (for frontend consumption).
     * Route: GET /api/units
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listUnits()
    {
        // Simple list retrieval for API, fetching only necessary fields
        $units = Unit::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($units);
    }
}
