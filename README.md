# Sistema de Gestão Financeira Interna - Instituto JP II

Front-end demonstrativo em Laravel Blade + Tailwind CSS para o sistema interno de controle de caixa do Instituto de Ação Social João Paulo II.

## Telas implementadas

- Login administrativo demonstrativo
- Painel principal
- Novo lançamento
- Extrato detalhado
- Relatórios

## Como rodar quando PHP, Composer e Node estiverem instalados

```powershell
composer install
copy .env.example .env
php artisan key:generate
npm install
npm run dev
php artisan serve
```

Depois acesse `http://127.0.0.1:8000/login`.