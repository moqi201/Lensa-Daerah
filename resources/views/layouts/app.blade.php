<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#121A24">
    <title>@yield('title', 'LENSA DAERAH - Portal Transparansi Open Data Jawa Barat')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS CDN + Chart.js (tidak berubah dari versi sebelumnya) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        /*
         * Token desain. Palet mengacu ke AWS User Group:
         *  - ink       : permukaan gelap (#1C2836 / #232F3E)
         *  - aws, electric, iris, amethyst, orchid : warna aksen untuk gradien dan isian
         *  - mist, lilac : tint terang untuk TEKS aksen di atas latar gelap (kontras lebih baik
         *                  daripada memakai biru/ungu aksen langsung sebagai warna teks)
         */
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        ink: { 950: '#121A24', 900: '#1C2836', 800: '#232F3E', 700: '#2D3B4E' },
                        aws: '#0073BB',
                        electric: '#0052CC',
                        iris: '#6F42C1',
                        amethyst: '#8C43FC',
                        orchid: '#A166FF',
                        mist: '#7CC4F0',
                        lilac: '#C9B6FF',
                    },
                    boxShadow: {
                        glass: '0 12px 40px -16px rgba(0,0,0,.6), inset 0 1px 0 rgba(255,255,255,.06)',
                    },
                },
            },
        };
    </script>

    <script>
        /* Splash tampil sekali per sesi; dilewati bila pengguna memilih reduced motion. */
        (function () {
            try {
                if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                if (sessionStorage.getItem('lensa-splash')) return;
                sessionStorage.setItem('lensa-splash', '1');
                document.documentElement.classList.add('splash-on');
            } catch (e) { /* storage dinonaktifkan: lewati splash */ }
        })();
    </script>

    <style>
        html { color-scheme: dark; scroll-padding-top: 5.5rem; }
        :focus-visible { outline: 2px solid #C9B6FF; outline-offset: 2px; }
        select.field option { background: #1C2836; color: #F1F5F9; }

        /* Latar: dua pendar halus dari palet, tetap di tempat saat halaman digulir */
        body::before {
            content: ''; position: fixed; inset: 0; z-index: -1; pointer-events: none;
            background:
                radial-gradient(56rem 34rem at 8% -8%, rgba(0, 115, 187, .22), transparent 62%),
                radial-gradient(48rem 32rem at 100% 6%, rgba(140, 67, 252, .16), transparent 62%);
        }

        /* Splash */
        #splash { display: none; }
        .splash-on #splash {
            position: fixed; inset: 0; z-index: 100;
            display: flex; align-items: center; justify-content: center;
            background:
                radial-gradient(40rem 26rem at 50% 42%, rgba(111, 66, 193, .28), transparent 65%),
                radial-gradient(34rem 22rem at 30% 70%, rgba(0, 115, 187, .22), transparent 65%),
                #121A24;
            animation: splash-exit .45s ease 1.55s forwards;
        }
        @keyframes splash-exit { to { opacity: 0; visibility: hidden; } }
        @keyframes lm-draw { from { stroke-dasharray: .01 100; } to { stroke-dasharray: 84 16; } }
        @keyframes lm-rise { from { opacity: 0; transform: scaleY(0); } to { opacity: 1; transform: none; } }
        @keyframes fade-up { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        @keyframes bar-run { from { transform: translateX(-100%); } to { transform: translateX(0); } }

        .splash-on .splash-logo .lm-arc { animation: lm-draw 1s cubic-bezier(.65, 0, .35, 1) both; }
        .splash-on .splash-logo .lm-bar { transform-box: fill-box; transform-origin: bottom; animation: lm-rise .45s ease both; }
        .splash-on .splash-logo .lm-bar:nth-of-type(1) { animation-delay: .65s; }
        .splash-on .splash-logo .lm-bar:nth-of-type(2) { animation-delay: .75s; }
        .splash-on .splash-logo .lm-bar:nth-of-type(3) { animation-delay: .85s; }
        .splash-on .splash-word { animation: fade-up .6s ease .5s both; }
        .splash-on .splash-tag  { animation: fade-up .6s ease .7s both; }
        .splash-on .splash-bar > span { animation: bar-run 1.4s cubic-bezier(.4, 0, .2, 1) .2s both; }

        @media (prefers-reduced-motion: reduce) {
            #splash { display: none !important; }
            *, *::before, *::after {
                animation-duration: .01ms !important; animation-iteration-count: 1 !important;
                transition-duration: .01ms !important; scroll-behavior: auto !important;
            }
        }
    </style>

    <!-- Komponen bersama: dipakai di semua halaman agar tidak ada duplikasi kelas -->
    <style type="text/tailwindcss">
        @layer components {
            .glass {
                @apply rounded-3xl border border-white/10 bg-white/[0.04] shadow-glass backdrop-blur-xl;
            }
            .chip {
                @apply inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold text-slate-200;
            }
            .btn-primary, .btn-ghost {
                @apply inline-flex min-h-[44px] items-center justify-center gap-2 rounded-2xl px-5 py-2.5 text-sm font-semibold transition;
            }
            .btn-primary {
                @apply bg-gradient-to-r from-electric via-iris to-amethyst text-white shadow-lg shadow-amethyst/20 hover:brightness-110;
            }
            .btn-ghost {
                @apply border border-white/15 bg-white/5 text-slate-100 hover:bg-white/10;
            }
            .field {
                @apply block w-full min-h-[44px] rounded-2xl border border-white/15 bg-ink-900/80 px-4 py-2.5 text-base text-slate-100 placeholder:text-slate-500 focus:border-lilac focus:outline-none focus:ring-2 focus:ring-lilac/40 sm:text-sm;
            }
            .nav-link {
                @apply inline-flex min-h-[40px] items-center gap-2 rounded-xl px-3.5 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white;
            }
            .nav-link[aria-current='page'] {
                @apply bg-white/10 text-white ring-1 ring-white/10;
            }
            .section-title {
                @apply text-2xl font-extrabold tracking-tight text-white sm:text-3xl;
            }
        }
    </style>
</head>
<body class="flex min-h-screen flex-col bg-ink-950 font-sans text-slate-300 antialiased selection:bg-amethyst selection:text-white">

    @php
        // Rute 'landing' bersifat opsional agar layout tetap jalan sebelum rute ditambahkan.
        $hasLanding = \Illuminate\Support\Facades\Route::has('landing');
        $homeUrl    = $hasLanding ? route('landing') : route('dashboard');

        $navItems = array_values(array_filter([
            $hasLanding ? ['label' => 'Beranda', 'href' => route('landing'), 'icon' => 'fa-house', 'active' => request()->routeIs('landing')] : null,
            ['label' => 'Dashboard', 'href' => route('dashboard'), 'icon' => 'fa-chart-pie', 'active' => request()->routeIs('dashboard')],
            ['label' => 'Direktori Puskesmas', 'href' => route('puskesmas.index'), 'icon' => 'fa-hospital-user', 'active' => request()->routeIs('puskesmas.index')],
        ]));
    @endphp

    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[110] focus:rounded-xl focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-ink-950">
        Lewati ke konten utama
    </a>

    @include('partials.splash')

    <!-- NAVBAR -->
    <header class="sticky top-0 z-40 border-b border-white/10 bg-ink-950/75 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ $homeUrl }}" class="flex items-center gap-3" aria-label="LENSA DAERAH, ke beranda">
                @include('partials.logo-mark', ['class' => 'h-9 w-9'])
                <span class="leading-tight">
                    <span class="block text-lg font-extrabold tracking-tight text-white">LENSA <span class="text-mist">DAERAH</span></span>
                    <span class="hidden text-[11px] font-medium text-slate-400 sm:block">Open Government Data Jabar</span>
                </span>
            </a>

            <nav aria-label="Navigasi utama" class="hidden items-center gap-1 md:flex">
                @foreach($navItems as $item)
                    <a href="{{ $item['href'] }}" class="nav-link" @if($item['active']) aria-current="page" @endif>
                        <i class="fa-solid {{ $item['icon'] }} text-mist" aria-hidden="true"></i> {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                <span class="chip hidden lg:inline-flex"><i class="fa-solid fa-server text-mist" aria-hidden="true"></i> Laravel REST API</span>
                <button type="button" id="nav-toggle"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/15 bg-white/5 text-slate-100 hover:bg-white/10 md:hidden"
                        aria-expanded="false" aria-controls="mobile-menu" aria-label="Buka menu">
                    <i class="fa-solid fa-bars" data-icon="open" aria-hidden="true"></i>
                    <i class="fa-solid fa-xmark hidden" data-icon="close" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden border-t border-white/10 bg-ink-950/95 md:hidden">
            <nav aria-label="Navigasi seluler" class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-3 sm:px-6">
                @foreach($navItems as $item)
                    <a href="{{ $item['href'] }}" class="nav-link min-h-[48px] text-base" @if($item['active']) aria-current="page" @endif>
                        <i class="fa-solid {{ $item['icon'] }} w-5 text-mist" aria-hidden="true"></i> {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    <!-- KONTEN -->
    <main id="main" class="flex-grow">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="mt-auto border-t border-white/10 bg-ink-950/80">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-[1.3fr_.7fr_1fr]">
                <div>
                    <a href="{{ $homeUrl }}" class="inline-flex items-center gap-3">
                        @include('partials.logo-mark', ['class' => 'h-10 w-10'])
                        <span class="text-xl font-extrabold tracking-tight text-white">LENSA <span class="text-mist">DAERAH</span></span>
                    </a>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-400">
                        Portal visualisasi transparansi Open Data pemerintah daerah untuk mewujudkan tata kelola publik yang terbuka dan akuntabel.
                    </p>
                    <p class="mt-4 max-w-md text-xs leading-relaxed text-slate-500">
                        Data diolah dari REST API Open Data Jawa Barat. Setiap halaman menampilkan status sumber dan catatan kualitas data agar jelas dari mana angka berasal.
                    </p>
                </div>

                <nav aria-label="Tautan footer">
                    <h2 class="text-sm font-bold text-white">Jelajahi</h2>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach($navItems as $item)
                            <li><a href="{{ $item['href'] }}" class="text-slate-400 transition hover:text-white">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </nav>

                <section aria-labelledby="footer-team" class="glass p-5">
                    <h2 id="footer-team" class="text-sm font-bold text-white">Disusun oleh Tim KinantiRaiUtami</h2>
                    <p class="mt-1 text-xs text-slate-400">Subtema: Open Government Data</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-300">
                        <li class="flex items-start gap-2.5"><i class="fa-solid fa-user-check mt-1 text-mist" aria-hidden="true"></i> <span>Ketua Tim - Kinanti Rai Utami - Universitas Pamulang</span></li>
                        <li class="flex items-start gap-2.5"><i class="fa-solid fa-user-group mt-1 text-mist" aria-hidden="true"></i> <span>Anggota 1 - Nikita Aidina Hidayat - Universitas Pamulang</span></li>
                        <li class="flex items-start gap-2.5"><i class="fa-solid fa-user-group mt-1 text-mist" aria-hidden="true"></i> <span>Anggota 2 - Siti Intan Nurfadilah - Universitas Pamulang</span></li>
                    </ul>
                </section>
            </div>

            <div class="mt-10 border-t border-white/10 pt-6 text-center text-xs text-slate-500">
                &copy; 2026 Tim KinantiRaiUtami - Universitas Pamulang. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Menu seluler
        (function () {
            var toggle = document.getElementById('nav-toggle');
            var menu = document.getElementById('mobile-menu');
            if (!toggle || !menu) return;

            function setOpen(open) {
                menu.classList.toggle('hidden', !open);
                toggle.setAttribute('aria-expanded', String(open));
                toggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
                toggle.querySelector('[data-icon="open"]').classList.toggle('hidden', open);
                toggle.querySelector('[data-icon="close"]').classList.toggle('hidden', !open);
            }

            toggle.addEventListener('click', function () {
                setOpen(toggle.getAttribute('aria-expanded') !== 'true');
            });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
            matchMedia('(min-width: 768px)').addEventListener('change', function (e) { if (e.matches) setOpen(false); });
        })();
    </script>

    @stack('scripts')
</body>
</html>