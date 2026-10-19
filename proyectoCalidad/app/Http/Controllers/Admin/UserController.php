<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->latest()->paginate(20);

        return view('admin.usuarios.index', compact('users'));
    }

    public function show(User $user): View
    {
        return view('admin.usuarios.show', compact('user'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403, 'No puedes cambiar tu propio rol.');

        $data = $request->validate([
            'role' => ['required', 'in:aprendiz,instructor,administrador'],
        ]);

        if ($user->isAdministrador()
            && $data['role'] !== 'administrador'
            && User::where('role', 'administrador')->count() <= 1) {
            return back()->withErrors([
                'role' => 'Debe mantenerse al menos un administrador en el sistema.',
            ]);
        }

        $user->update(['role' => $data['role']]);

        return redirect()
            ->route('admin.usuarios.show', $user)
            ->with('success', 'El rol se actualizó correctamente.');
    }
}