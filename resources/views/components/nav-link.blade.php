@props([
    'href',
    'active' => false,
    'icon' => null,
])

@php
    $iconClasses = 'sidebar-icon h-5 w-5 shrink-0';
@endphp

<a href="{{ $href }}" @class([
    'sidebar-nav-link group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-bold transition',
    'bg-navy-900 text-white shadow-soft' => $active,
    'text-slate-600 hover:bg-slate-50 hover:text-navy-900' => ! $active,
]) title="{{ trim($slot) }}">
    @switch($icon)
        @case('dashboard')
            <svg class="{{ $iconClasses }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="7" height="8" rx="1.5" />
                <rect x="14" y="3" width="7" height="5" rx="1.5" />
                <rect x="14" y="12" width="7" height="9" rx="1.5" />
                <rect x="3" y="15" width="7" height="6" rx="1.5" />
            </svg>
            @break

        @case('statement')
            <svg class="{{ $iconClasses }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z" />
                <path d="M9 8h6" />
                <path d="M9 12h6" />
                <path d="M9 16h4" />
            </svg>
            @break

        @case('entry')
            <svg class="{{ $iconClasses }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <path d="M12 8v8" />
                <path d="M8 12h8" />
            </svg>
            @break

        @case('reports')
            <svg class="{{ $iconClasses }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 19V5" />
                <path d="M4 19h16" />
                <rect x="7" y="11" width="3" height="5" rx="1" />
                <rect x="12" y="7" width="3" height="9" rx="1" />
                <rect x="17" y="9" width="3" height="7" rx="1" />
            </svg>
            @break

        @case('admins')
            <svg class="{{ $iconClasses }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" />
                <circle cx="9.5" cy="7" r="4" />
                <path d="M19 8v6" />
                <path d="M22 11h-6" />
            </svg>
            @break

        @default
            <span class="sidebar-icon h-2.5 w-2.5 shrink-0 rounded-full bg-current"></span>
    @endswitch

    <span class="sidebar-label truncate">{{ $slot }}</span>
</a>
