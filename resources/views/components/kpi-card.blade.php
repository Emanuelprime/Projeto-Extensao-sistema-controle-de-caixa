@props([
    'label',
    'value',
    'detail' => null,
    'tone' => 'blue',
])

@php
    $toneClasses = [
        'blue' => 'bg-blue-50 text-action',
        'green' => 'bg-emerald-50 text-emerald-700',
        'red' => 'bg-red-50 text-danger',
        'neutral' => 'bg-slate-50 text-slate-600',
    ][$tone] ?? 'bg-blue-50 text-action';
@endphp

<section class="surface p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="eyebrow">{{ $label }}</p>
            <p class="mt-3 font-display text-3xl font-black tracking-tight text-ink">{{ $value }}</p>
        </div>
        <span class="rounded-lg px-3 py-2 text-xs font-extrabold {{ $toneClasses }}">●</span>
    </div>
    @if ($detail)
        <p class="mt-4 text-sm font-semibold text-muted">{{ $detail }}</p>
    @endif
</section>
