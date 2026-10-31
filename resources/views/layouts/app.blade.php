<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LENSA DAERAH - Portal Transparansi Open Data Jawa Barat</title>

    <!-- Tailwind CSS CDN Fallback + Vite -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Lora:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Lora', 'serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-brand-500 selection:text-white min-h-screen flex flex-col">

    <!-- NAVBAR -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 via-brand-600 to-emerald-400 flex items-center justify-center text-white shadow-md shadow-brand-600/20">
                        <i class="fa-solid fa-eye text-lg"></i>
                    </div>
                    <div>
                        <span class="font-serif text-xl font-extrabold text-slate-900 tracking-tight">LENSA <span class="text-brand-600">DAERAH</span></span>
                        <span class="block text-[10px] text-slate-500 font-semibold tracking-wider uppercase">Open Government Data Jabar</span>
                    </div>
                </a>

                <!-- MENU NAVIGASI PANDUAN HALAMAN -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-700 border border-brand-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-chart-pie mr-1.5 text-brand-600"></i> Dashboard Transparansi
                    </a>
                    <a href="{{ route('puskesmas.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('puskesmas.index') ? 'bg-brand-50 text-brand-700 border border-brand-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-hospital-user mr-1.5 text-brand-600"></i> Direktori & Maps Puskesmas
                    </a>
                </nav>
            </div>

            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Laravel REST API Active</span>
                </span>
            </div>
        </div>
    </header>

    <!-- CONTENT BODY -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="mt-auto py-10 bg-slate-950 text-white border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div>
                    <div class="inline-block px-3 py-1 rounded-md bg-brand-500/20 text-brand-400 text-xs font-bold mb-3">
                        Subtema: Open Government Data • Tim KinantiRaiUtami
                    </div>
                    <h3 class="font-serif text-2xl font-bold">LENSA DAERAH</h3>
                    <p class="mt-2 text-slate-400 text-sm leading-relaxed max-w-md">
                        Portal visualisasi transparansi Open Data Pemerintah daerah untuk mewujudkan tata kelola publik yang terbuka dan akuntabel.
                    </p>
                </div>

                <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Disusun Oleh Tim KinantiRaiUtami:</h4>
                    <ul class="space-y-2 text-sm text-slate-300 font-medium">
                        <li class="flex items-center gap-2.5"><i class="fa-solid fa-user-check text-brand-500"></i> Ketua Tim - Kinanti Rai Utami - Universitas Pamulang</li>
                        <li class="flex items-center gap-2.5"><i class="fa-solid fa-user-group text-brand-500"></i> Anggota 1 - Nikita Aidina Hidayat - Universitas Pamulang</li>
                        <li class="flex items-center gap-2.5"><i class="fa-solid fa-user-group text-brand-500"></i> Anggota 2 - Siti Intan Nurfadilah - Universitas Pamulang</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-slate-800/80 text-center text-xs text-slate-500">
                &copy; 2026 Tim KinantiRaiUtami - Universitas Pamulang. All rights reserved.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>