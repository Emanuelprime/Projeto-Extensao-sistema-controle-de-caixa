<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Garante que só admins acessam qualquer ação deste controller.
     */
    private function authorizeAdmin(): void
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Apenas administradores podem gerenciar usuários.');
        }
    }

    // ─── Listagem ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = User::orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    // ─── Formulário de criação ────────────────────────────────────────────────

    public function create()
    {
        $this->authorizeAdmin();
        return view('users.create');
    }

    // ─── Persistência ─────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role'     => ['required', 'in:admin,operador'],
            'status'   => ['required', 'in:ativo,bloqueado'],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'status'   => $validated['status'],
        ]);

        return redirect()->route('users.index')
                         ->with('success', 'Usuário criado com sucesso!');
    }

    // ─── Formulário de edição ─────────────────────────────────────────────────

    public function edit(User $user)
    {
        $this->authorizeAdmin();
        return view('users.edit', compact('user'));
    }

    // ─── Atualização ──────────────────────────────────────────────────────────

    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $rules = [
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role'   => ['required', 'in:admin,operador'],
            'status' => ['required', 'in:ativo,bloqueado'],
        ];

        // Senha só obrigatória se preenchida
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        }

        $validated = $request->validate($rules);

        $data = [
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'role'   => $validated['role'],
            'status' => $validated['status'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Impede admin de se bloquear
        if ($user->id === Auth::id() && $validated['status'] === 'bloqueado') {
            return back()->withErrors(['status' => 'Você não pode bloquear a própria conta.']);
        }

        $user->update($data);

        return redirect()->route('users.index')
                         ->with('success', 'Usuário atualizado com sucesso!');
    }
}
