{{--
    Splash screen LENSA DAERAH.
    - Tampil sekali per sesi browser (diatur oleh skrip di <head> layouts/app.blade.php).
    - Tidak tampil bila pengguna memilih prefers-reduced-motion.
    - Tanpa JavaScript tetap aman: elemen tersembunyi secara default, dan animasi CSS
      menutupnya otomatis (maksimal sekitar 2 detik).
    - Klik atau tekan Esc untuk melewati.
--}}
<div id="splash" aria-hidden="true">
    <div class="relative flex flex-col items-center px-6 text-center">
        @include('partials.logo-mark', ['class' => 'splash-logo h-24 w-24 sm:h-28 sm:w-28'])

        <p class="splash-word mt-6 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
            LENSA <span class="text-mist">DAERAH</span>
        </p>
        <p class="splash-tag mt-2 text-sm text-slate-400">Portal transparansi Open Data Jawa Barat</p>

        <div class="splash-bar mt-8 h-0.5 w-40 overflow-hidden rounded-full bg-white/10">
            <span class="block h-full w-full bg-gradient-to-r from-aws via-iris to-orchid"></span>
        </div>
    </div>
</div>

<script>
    (function () {
        var root = document.documentElement;
        var splash = document.getElementById('splash');
        if (!splash || !root.classList.contains('splash-on')) return;

        var closed = false;
        function close() {
            if (closed) return;
            closed = true;
            splash.style.animation = 'splash-exit .25s ease forwards';
            setTimeout(function () { root.classList.remove('splash-on'); }, 260);
        }

        splash.addEventListener('click', close);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
        // Animasi CSS menutup splash otomatis; ini hanya membersihkan kelas setelahnya.
        setTimeout(function () { closed = true; root.classList.remove('splash-on'); }, 2100);
    })();
</script>