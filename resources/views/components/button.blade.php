{{-- resources/views/components/button.blade.php --}}
@props([
    'variant' => 'solid', // solid | outline | ghost | flat | link
    'color' => 'primary', // primary | secondary | success | warning | danger | neutral
    'size' => 'md', // sm | md | lg
    'as' => null, // 'a' | 'button' (auto dari href)
    'href' => null, // jika diisi otomatis render <a>
    'type' => 'button', // button | submit | reset
    'full' => false, // true -> w-full
    'disabled' => false, // disabled state
    'rounded' => 'xl', // sm | md | lg | xl | 2xl | full
])

@php
    // mapping warna ke palette Tailwind
    $palette = [
        'primary' => 'blue',
        'secondary' => 'slate',
        'success' => 'green',
        'warning' => 'amber',
        'danger' => 'rose',
        'neutral' => 'gray',
    ];
    $p = $palette[$color] ?? $palette['primary'];

    // cek apakah ada teks di slot (untuk icon-only)
    $hasText = trim((string) $slot) !== '';

    // ukuran
    $sizeClasses =
        [
            'sm' => $hasText ? 'h-9 px-3 text-sm' : 'h-9 w-9 text-sm',
            'md' => $hasText ? 'h-10 px-4 text-sm' : 'h-10 w-10 text-sm',
            'lg' => $hasText ? 'h-12 px-5 text-base' : 'h-12 w-12 text-base',
        ][$size] ?? 'h-10 px-4 text-sm';

    // base class
    $base = implode(' ', [
        'inline-flex items-center justify-center gap-2 select-none',
        'font-medium transition',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
        'disabled:opacity-50 disabled:pointer-events-none',
        'rounded-' . $rounded,
        $full ? 'w-full' : '',
        // ring mengikuti warna
        "focus-visible:ring-{$p}-500 dark:focus-visible:ring-{$p}-400",
        // ring-offset untuk dark
        'dark:focus-visible:ring-offset-gray-900',
    ]);

    // variant styles
    $variants = [
        'solid' => implode(' ', [
            "bg-{$p}-600 hover:bg-{$p}-700 text-white",
            "dark:bg-{$p}-500 dark:hover:bg-{$p}-600 dark:text-white",
        ]),
        'outline' => implode(' ', [
            "border border-{$p}-600 text-{$p}-700 hover:bg-{$p}-50",
            "dark:border-{$p}-400 dark:text-{$p}-300 dark:hover:bg-{$p}-950",
        ]),
        'ghost' => implode(' ', ["text-{$p}-700 hover:bg-{$p}-50", "dark:text-{$p}-300 dark:hover:bg-{$p}-950"]),
        'flat' => implode(' ', [
            "bg-{$p}-100 text-{$p}-800 hover:bg-{$p}-200",
            "dark:bg-{$p}-950 dark:text-{$p}-200 dark:hover:bg-{$p}-900",
        ]),
        'link' => implode(' ', [
            "text-{$p}-600 hover:underline",
            "dark:text-{$p}-400",
            'p-0 h-auto align-baseline', // link style
        ]),
    ];

    $variantClasses = $variants[$variant] ?? $variants['solid'];

    // padding/height final (untuk link -> jangan pakai height tetap)
    $sizing = $variant === 'link' ? '' : $sizeClasses;

    // kelas untuk ikon (svg atau apapun di slot icon)
    $iconSize =
        [
            'sm' => 'size-4',
            'md' => 'size-5',
            'lg' => 'size-5',
        ][$size] ?? 'size-5';

    // element tag
    $tag = $href ? 'a' : ($as ?: 'button');

    // merge classes eksternal
    $classes = trim($base . ' ' . $variantClasses . ' ' . $sizing);
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    @if ($tag === 'button') type="{{ $type }}" @endif
    @if ($disabled) disabled @endif {{ $attributes->merge(['class' => $classes]) }}>
    @isset($icon)
        <span class="{{ $iconSize }} shrink-0">
            {{ $icon }}
        </span>
    @endisset

    @if ($hasText)
        <span>{{ $slot }}</span>
    @endif

    @isset($trailing)
        <span class="{{ $iconSize }} shrink-0">
            {{ $trailing }}
        </span>
    @endisset
    </{{ $tag }}>
