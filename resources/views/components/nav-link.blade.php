@props([
    'href',
    'active' => false,
])

<a href="{{ $href }}" @class([
    'group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-bold transition',
    'bg-navy-900 text-white shadow-soft' => $active,
    'text-slate-600 hover:bg-slate-50 hover:text-navy-900' => ! $active,
])>
    <span @class([
        'h-2.5 w-2.5 rounded-full',
        'bg-white' => $active,
        'bg-slate-300 group-hover:bg-action' => ! $active,
    ])></span>
    {{ $slot }}
</a>
