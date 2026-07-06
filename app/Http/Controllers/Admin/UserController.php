<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * List all users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_filter')) {
            $roleFilter = $request->input('role_filter');
            if ($roleFilter === 'admin') {
                $query->where('is_admin', true);
            } elseif ($roleFilter === 'SCK') {
                $query->where('role', 'SCK')->where('is_admin', false);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('is_admin')->orWhere('is_admin', false);
                })->where(function ($q) {
                    $q->whereNull('role')->orWhere('role', '!=', 'SCK');
                });
            }
        }

        $users = $query->orderBy('name')->paginate(25)->appends(request()->query());


        return view('admin.users.index', compact('users'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'user_role' => ['required', 'in:user,admin,SCK'],
        ]);

        $isAdmin = $validated['user_role'] === 'admin';
        $role    = $validated['user_role'] === 'SCK' ? 'SCK' : null;

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $isAdmin,
            'role'     => $role,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Benutzer \"{$validated['name']}\" wurde erfolgreich erstellt.");
    }

    /**
     * Show edit form.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update existing user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'user_role' => ['required', 'in:user,admin,SCK'],
        ]);

        $isAdmin = $validated['user_role'] === 'admin';
        $role    = $validated['user_role'] === 'SCK' ? 'SCK' : null;

        $user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'is_admin' => $isAdmin,
            'role'     => $role,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Benutzer \"{$user->name}\" wurde aktualisiert.");
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Du kannst deinen eigenen Account nicht löschen.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Benutzer \"{$name}\" wurde gelöscht.");
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Passwort von \"{$user->name}\" wurde zurückgesetzt.");
    }
}
