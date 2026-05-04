<x-admin-layout
    title="Usuários"
    subtitle="Gerencie os operadores e administradores com acesso ao sistema."
>
    @if(Auth::user()->isAdmin())
    <x-slot:actions>
        <a href="{{ route('users.create') }}" class="primary-button">+ Novo usuário</a>
    </x-slot:actions>
    @endif

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm font-medium text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filtros --}}
    <section class="quiet-surface p-5">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="grid gap-4 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
                <label class="block">
                    <span class="field-label">Buscar</span>
                    <input class="field-control" type="search" name="search"
                           value="{{ request('search') }}" placeholder="Nome ou e-mail...">
                </label>
                <label class="block">
                    <span class="field-label">Perfil</span>
                    <select class="field-control" name="role">
                        <option value="">Todos os perfis</option>
                        <option value="admin"    @selected(request('role') === 'admin')>Administrador</option>
                        <option value="operador" @selected(request('role') === 'operador')>Operador</option>
                    </select>
                </label>
                <label class="block">
                    <span class="field-label">Status</span>
                    <select class="field-control" name="status">
                        <option value="">Todos os status</option>
                        <option value="ativo"     @selected(request('status') === 'ativo')>Ativo</option>
                        <option value="bloqueado" @selected(request('status') === 'bloqueado')>Bloqueado</option>
                    </select>
                </label>
                <button type="submit" class="primary-button">Filtrar</button>
            </div>
        </form>
    </section>

    <section class="mt-6 surface overflow-hidden">
        <div class="flex items-center justify-between border-b border-line px-6 py-5">
            <div>
                <p class="eyebrow">Usuários cadastrados</p>
                <h2 class="mt-1 text-xl font-extrabold text-ink">
                    {{ $users->total() }} {{ $users->total() === 1 ? 'usuário' : 'usuários' }}
                </h2>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead class="table-head">
                    <tr>
                        <th class="px-6 py-3 text-left">Nome</th>
                        <th class="px-6 py-3 text-left">E-mail</th>
                        <th class="px-6 py-3 text-left">Perfil</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Criado em</th>
                        @if(Auth::user()->isAdmin())
                        <th class="px-6 py-3">Ação</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="transition hover:bg-slate-50">
                            <td class="table-cell font-bold text-ink">
                                {{ $user->name }}
                                @if($user->id === Auth::id())
                                    <span class="ml-1 text-xs font-semibold text-muted">(você)</span>
                                @endif
                            </td>
                            <td class="table-cell text-slate-600">{{ $user->email }}</td>
                            <td class="table-cell">
                                @if($user->role === 'admin')
                                    <span class="inline-flex items-center rounded-full bg-navy-900/10 px-2 py-0.5 text-xs font-extrabold text-navy-900">Admin</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-extrabold text-slate-600">Operador</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                @if($user->status === 'ativo')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-extrabold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Ativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-extrabold text-red-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Bloqueado
                                    </span>
                                @endif
                            </td>
                            <td class="table-cell text-slate-500">{{ $user->created_at->format('d/m/Y') }}</td>
                            @if(Auth::user()->isAdmin())
                            <td class="table-cell text-center">
                                <a href="{{ route('users.edit', $user->id) }}" class="ghost-button text-xs">Editar</a>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-cell py-10 text-center text-sm text-muted">
                                Nenhum usuário encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-5">{{ $users->links() }}</div>
        @endif
    </section>
</x-admin-layout>
