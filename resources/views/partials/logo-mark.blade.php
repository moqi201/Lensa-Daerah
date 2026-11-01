{{--
    Logo LENSA DAERAH.
    Variabel opsional: $class (ukuran/kelas Tailwind, default 'h-10 w-10').

    Cara mengganti dengan logo resmi: letakkan file di
    public/images/logo.svg (atau logo.png / logo.webp). Partial ini otomatis memakainya.
    Bila file tidak ada, dipakai ikon fallback: cincin lensa + grafik batang.
--}}
@php
    $logoFile = collect(['svg', 'png', 'webp'])
        ->map(fn ($ext) => "images/logo.$ext")
        ->first(fn ($path) => file_exists(public_path($path)));

    // ID gradien dibuat unik agar beberapa logo di satu halaman tidak saling bentrok.
    $gid = 'lm' . substr(md5(uniqid('', true)), 0, 6);
@endphp

@if($logoFile)
    <img src="{{ asset($logoFile) }}" alt="" class="{{ $class ?? 'h-10 w-10' }} object-contain">
@else
    <svg viewBox="0 0 64 64" fill="none" class="{{ $class ?? 'h-10 w-10' }}" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="{{ $gid }}" x1="10" y1="56" x2="56" y2="8" gradientUnits="userSpaceOnUse">
                <stop stop-color="#0073BB"/>
                <stop offset=".55" stop-color="#6F42C1"/>
                <stop offset="1" stop-color="#A166FF"/>
            </linearGradient>
        </defs>
        <circle class="lm-arc" cx="32" cy="32" r="26" stroke="url(#{{ $gid }})" stroke-width="5"
                stroke-linecap="round" pathLength="100" stroke-dasharray="84 16" transform="rotate(126 32 32)"/>
        <rect class="lm-bar" x="21" y="34" width="5.5" height="9"  rx="2.75" fill="url(#{{ $gid }})"/>
        <rect class="lm-bar" x="29.25" y="28" width="5.5" height="15" rx="2.75" fill="url(#{{ $gid }})"/>
        <rect class="lm-bar" x="37.5" y="21" width="5.5" height="22" rx="2.75" fill="url(#{{ $gid }})"/>
    </svg>
@endif