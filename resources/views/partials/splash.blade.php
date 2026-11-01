{{--
    Splash screen LENSA DAERAH.
    - Logo di tengah dikelilingi loading spinner (chasing dots).
    - Spinner memudar, lalu logo bergeser ke kiri secara sangat smooth.
    - Teks "LENSA DAERAH" terungkap dari tengah ke kanan dengan jarak aman (tanpa tabrakan).
    - Jeda waktu baca sesuai keinginan, lalu fade-out.
--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<div id="splash" aria-hidden="true" class="fixed inset-0 z-[100] flex items-center justify-center bg-ink-950">
    <!-- Wadah utama di tengah layar -->
    <div class="relative flex items-center justify-center">
        
        <!-- Logo & Spinner (Awalnya tepat di tengah) -->
        <div id="splash-logo" class="relative z-10 shrink-0 flex items-center justify-center">
            
            <!-- Efek Loading "Chasing Dots" -->
            <svg id="loading-spinner" class="absolute w-28 h-28 sm:w-36 sm:h-36 animate-[spin_1.2s_linear_infinite] text-white" viewBox="0 0 100 100">
                <circle cx="50" cy="4" r="6" fill="currentColor" />
                <circle cx="82.5" cy="17.5" r="4.5" fill="currentColor" opacity="0.8" />
                <circle cx="96" cy="50" r="3.5" fill="currentColor" opacity="0.6" />
                <circle cx="82.5" cy="82.5" r="2.5" fill="currentColor" opacity="0.4" />
                <circle cx="50" cy="96" r="2" fill="currentColor" opacity="0.2" />
                <circle cx="17.5" cy="82.5" r="1.5" fill="currentColor" opacity="0.1" />
            </svg>

            <!-- Logo -->
            <div class="relative z-10">
                @include('partials.logo-mark', ['class' => 'h-20 w-20 sm:h-24 sm:w-24'])
            </div>
        </div>
        
        <!-- Teks (Diposisikan tepat di sebelah kanan logo dengan padding/gap yang pas) -->
        <div id="splash-text" class="absolute left-0 text-left opacity-0 pl-6 sm:pl-8" style="visibility: hidden;">
            <div class="whitespace-nowrap">
                <p class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    LENSA <span class="text-mist">DAERAH</span>
                </p>
                <p class="mt-1 text-sm text-slate-400">Portal transparansi Open Data Jawa Barat</p>
            </div>
        </div>

    </div>
</div>

<script>
    (function () {
        var root = document.documentElement;
        var splash = document.getElementById('splash');
        if (!splash || !root.classList.contains('splash-on')) return;

        // Kunci scroll saat splash screen berjalan
        document.body.style.overflow = 'hidden';

        var closed = false;
        function closeSplash() {
            if (closed) return;
            closed = true;
            
            // Animasi memudar saat splash screen ditutup
            gsap.to(splash, {
                opacity: 0,
                duration: 0.5,
                ease: "power2.inOut",
                onComplete: function() {
                    root.classList.remove('splash-on');
                    splash.style.display = 'none';
                    document.body.style.overflow = ''; // Kembalikan scroll halaman
                }
            });
        }

        // --- KOREOGRAFI ANIMASI BUKA KIRI-KANAN (EFEK TIRAI / SPLIT) ---
        var tl = gsap.timeline();

        // 1. Logo diam di tengah sejenak (1.5 detik) sambil spinner berputar
        tl.to("#splash-logo", { duration: 1.5 })
        
        // 2. Fade out perlahan pada loading spinner agar menghilang dengan mulus
        .to("#loading-spinner", {
            opacity: 0,
            duration: 0.4,
            ease: "power2.out"
        })

        // 3. Logo bergeser ke kiri dan teks terbuka ke kanan secara bersamaan dan mulus
        .to("#splash-logo", {
            x: -110, // Jarak geser logo ke kiri
            duration: 1.2,
            ease: "power3.inOut"
        }, "animasi")

        .to("#splash-text", {
            visibility: "visible",
            opacity: 1,
            x: 20, // Jarak geser teks ke kanan dari titik tengah
            duration: 1.2,
            ease: "power3.inOut"
        }, "animasi");

        // Tutup jika layar diklik atau tombol Escape ditekan
        splash.addEventListener('click', closeSplash);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSplash(); });
        
        // Jeda waktu tampil (misal: 35 detik untuk membaca, silakan ubah sesuai kebutuhan)
        setTimeout(closeSplash, 80000); 
    })();
</script>