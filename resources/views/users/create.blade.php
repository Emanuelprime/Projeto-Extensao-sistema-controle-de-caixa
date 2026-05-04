<x-admin-layout
    title="Novo Usuário"
    subtitle="Crie uma conta de acesso para um operador ou administrador do sistema."
>
    <x-slot:actions>
        <a href="{{ route('users.index') }}" class="secondary-button">Voltar à listagem</a>
    </x-slot:actions>

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('users.store') }}" class="surface p-6 sm:p-8">
            @csrf

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

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block md:col-span-2">
                    <span class="field-label">Nome completo</span>
                    <input name="name" required class="field-control" type="text"
                           placeholder="Ex: João da Silva" value="{{ old('name') }}">
                </label>

                <label class="block md:col-span-2">
                    <span class="field-label">E-mail</span>
                    <input name="email" required class="field-control" type="email"
                           placeholder="usuario@exemplo.com" value="{{ old('email') }}">
                </label>

                <label class="block">
                    <span class="field-label">Senha</span>
                    <input name="password" required class="field-control" type="password"
                           placeholder="Mínimo 8 caracteres">
                </label>

                <label class="block">
                    <span class="field-label">Confirmar senha</span>
                    <input name="password_confirmation" required class="field-control" type="password"
                           placeholder="Repita a senha">
                </label>

                <label class="block">
                    <span class="field-label">Perfil de acesso</span>
                    <select name="role" class="field-control">
                        <option value="operador" @selected(old('role') === 'operador')>Operador</option>
                        <option value="admin"    @selected(old('role') === 'admin')>Administrador</option>
                    </select>
                </label>

                <label class="block">
                    <span class="field-label">Status da conta</span>
                    <select name="status" class="field-control">
                        <option value="ativo"     @selected(old('status', 'ativo') === 'ativo')>Ativo</option>
                        <option value="bloqueado" @selected(old('status') === 'bloqueado')>Bloqueado</option>
                    </select>
                </label>
            </div>

            <div class="mt-6 rounded-lg bg-blue-50 p-4 text-sm font-semibold text-action">
                <strong>Atenção:</strong> Administradores têm acesso total ao sistema, incluindo a criação e edição de outros usuários.
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('users.index') }}" class="secondary-button text-center">Cancelar</a>
                <button type="submit" class="primary-button">Criar usuário</button>
            </div>
        </form>
    </div>
</x-admin-layout>
