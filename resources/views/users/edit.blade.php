<x-admin-layout
    title="Editar Usuário"
    subtitle="Atualize os dados, perfil de acesso e status da conta."
>
    <x-slot:actions>
        <a href="{{ route('users.index') }}" class="secondary-button">Voltar à listagem</a>
    </x-slot:actions>

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('users.update', $user->id) }}" class="surface p-6 sm:p-8">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm font-medium text-red-800">
                    <p class="font-extrabold">Corrija os erros abaixo:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm font-medium text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block md:col-span-2">
                    <span class="field-label">Nome completo</span>
                    <input name="name" required class="field-control" type="text"
                           value="{{ old('name', $user->name) }}">
                </label>

                <label class="block md:col-span-2">
                    <span class="field-label">E-mail</span>
                    <input name="email" required class="field-control" type="email"
                           value="{{ old('email', $user->email) }}">
                </label>

                <label class="block">
                    <span class="field-label">Nova senha <span class="text-muted font-normal">(deixe em branco para manter)</span></span>
                    <input name="password" class="field-control" type="password"
                           placeholder="Mínimo 8 caracteres">
                </label>

                <label class="block">
                    <span class="field-label">Confirmar nova senha</span>
                    <input name="password_confirmation" class="field-control" type="password"
                           placeholder="Repita a nova senha">
                </label>

                <label class="block">
                    <span class="field-label">Perfil de acesso</span>
                    <select name="role" class="field-control">
                        <option value="operador" @selected(old('role', $user->role) === 'operador')>Operador</option>
                        <option value="admin"    @selected(old('role', $user->role) === 'admin')>Administrador</option>
                    </select>
                </label>

                <label class="block">
                    <span class="field-label">Status da conta</span>
                    <select name="status" class="field-control">
                        <option value="ativo"     @selected(old('status', $user->status) === 'ativo')>Ativo</option>
                        <option value="bloqueado" @selected(old('status', $user->status) === 'bloqueado')>Bloqueado</option>
                    </select>
                    @if($user->id === Auth::id())
                        <p class="mt-1 text-xs font-semibold text-amber-600">Você não pode bloquear a própria conta.</p>
                    @endif
                </label>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('users.index') }}" class="secondary-button text-center">Cancelar</a>
                <button type="submit" class="primary-button">Salvar alterações</button>
            </div>
        </form>
    </div>
</x-admin-layout>
