@props(['status'])

@php
    $statusKey = str($status)->lower()->ascii()->toString();
    $class = match ($statusKey) {
        'liquidado', 'processado' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'pendente', 'agendado' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'atrasado', 'falha' => 'bg-red-50 text-danger ring-red-200',
        default => 'bg-slate-50 text-slate-600 ring-slate-200',
    };
@endphp

<span class="inline-flex items-center rounded-full px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] ring-1 {{ $class }}">
    {{ $status }}
</span>
