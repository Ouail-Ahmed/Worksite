<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        // Redirect if already authenticated
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        return view('auth.login');
    }

    /**
     * Handle the login attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectToDashboard();
        }

        // Return back with input and a specific error message
        return back()->withInput($request->only('username'))->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle the logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        // Typically, registration should be disabled or protected in production
        return view('auth.register');
    }

    /**
     * Handle the registration submission.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:100|unique:users,username',
            // Enforce minimum length and confirmation (requires password_confirmation field in view)
            'password' => 'required|string|min:8|confirmed',
            // FIX: Role must match database ENUM values ('agent', 'directeur')
            'role'     => ['required', Rule::in(['agent', 'directeur'])],
        ]);

        User::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        return redirect()->route('login')->with('success', 'Account created successfully! Please log in.');
    }

    /**
     * Retrieves the currently authenticated user's details.
     * Used for API or frontend status checks.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthenticatedUser()
    {
        if (Auth::check()) {
            // Note: Ensure the User model hides the 'password' field using $hidden
            return response()->json([
                'user' => [
                    'id'       => Auth::id(),
                    'username' => Auth::user()->username,
                    'role'     => Auth::user()->role,
                ]
            ]);
        }

        // If not authenticated, return a 401 response
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    /**
     * Redirects the authenticated user to the appropriate dashboard based on their role.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToDashboard()
    {
        $user = Auth::user();

        if ($user->role === 'directeur') {
            // Director/Admin goes to the full admin dashboard
            return redirect()->route('dashboard.director');
        }

        if ($user->role === 'agent') {
            // Agent goes to the main operational dashboard (e.g., units index)
            return redirect()->route('units.index');
        }

        // Default fallback (should be rare)
        return redirect('dashboard');
    }
}
