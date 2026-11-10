@extends('layouts.app')

@section('title', 'Direktori Puskesmas Jawa Barat - LENSA DAERAH')

@push('styles')
<style>
    .loading-pulse { animation: directoryPulse 1.5s ease-in-out infinite; }
    @keyframes directoryPulse { 50% { opacity: .55; } }
</style>
@endpush

@section('content')
@php
    $meta = $data['meta'] ?? [];
    $kabOptions = $meta['kabupaten_options'] ?? config('puskesmas.reference_regions', []);
    $selectedKabupaten = $selectedKabupaten ?? 'SEMUA';
    $searchQuery = $searchQuery ?? '';
    $isRadiusSearch = (bool) ($meta['radius_applied'] ?? false);

    // Saat pertama kali membuka halaman tanpa filter, tampilkan beberapa data
    // sampai browser selesai meminta izin lokasi.
    $isInitialView = $selectedKabupaten === 'SEMUA'
        && $searchQuery === ''
        && !$isRadiusSearch;
    $puskesmasList = $isInitialView
        ? array_slice($data['items'] ?? [], 0, 8)
        : ($data['items'] ?? []);
@endphp

<section class="border-b border-white/10" aria-labelledby="directory-title">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        
        <!-- Badge: Menghapus uppercase, memakai normal case seperti di gambar, ukuran text-sm -->
        <span class="chip inline-flex items-center gap-2 rounded-full border border-white/10 px-3 py-1 text-sm font-medium">
            <i class="fa-solid fa-hospital text-mist" aria-hidden="true"></i>
            Direktori Fasilitas Kesehatan
            <span id="lokasi-text" class="font-semibold text-indigo-300"></span>
        </span>

        <!-- Judul: Dibuat sangat besar (5xl/6xl), tebal (extrabold), dan spasi antar huruf rapat (tracking-tight) seperti referensi -->
        <h1 id="dashboard-title" class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Akses Cepat Lokasi Puskesmas Seluruh Jawa Barat
        </h1>
        
        <!-- Deskripsi: Dibuat lebih besar (text-lg atau text-xl) agar proporsional mendampingi judul besar -->
        <p class="mt-5 max-w-3xl text-lg font-normal leading-relaxed text-slate-300 sm:text-xl">
            Temukan Puskesmas terdekat dalam radius 20 KM atau cari seluruh data berdasarkan nama, alamat, kecamatan, dan Kabupaten/Kota.
        </p>

        <!-- Form Pencarian -->
        <form method="GET" action="{{ route('puskesmas.index') }}" role="search" id="searchForm"
              class="glass mt-10 grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-[1fr_16rem_auto] lg:gap-5">
            <div>
                <label for="q" class="sr-only">Cari Puskesmas, alamat, atau kecamatan</label>
                <input type="search" id="q" name="q" value="{{ $searchQuery }}" autocomplete="off"
                       placeholder="Cari Puskesmas / Kecamatan / Alamat..."
                       class="field w-full text-base font-normal">
            </div>

            <div>
                <label for="kabupaten" class="sr-only">Filter Kabupaten atau Kota</label>
                <select id="kabupaten" name="kabupaten" class="field w-full text-base font-normal">
                    <option value="SEMUA" {{ $selectedKabupaten === 'SEMUA' ? 'selected' : '' }}>Semua Kab/Kota</option>
                    @foreach($kabOptions as $kab)
                        <option value="{{ $kab }}" {{ mb_strtoupper($selectedKabupaten) === mb_strtoupper($kab) ? 'selected' : '' }}>
                            {{ $kab }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-primary w-full text-base font-semibold">
                <i class="fa-solid fa-magnifying-glass mr-1" aria-hidden="true"></i> Cari
            </button>
        </form>

        <p id="location-help" class="mt-4 text-sm font-normal text-slate-400" role="status" aria-live="polite">
            Saat halaman dibuka tanpa filter, browser akan meminta izin lokasi untuk mencari Puskesmas terdekat.
        </p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" aria-label="Hasil pencarian Puskesmas">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-300" role="status">
            Menampilkan <strong class="text-white">{{ count($puskesmasList) }}</strong> Puskesmas
            @if($isRadiusSearch)
                <span class="text-indigo-300">(radius 0–20 KM dari lokasi Anda)</span>
            @elseif($selectedKabupaten !== 'SEMUA')
                <span class="text-indigo-300">(seluruh data Kabupaten/Kota terpilih)</span>
            @elseif($searchQuery !== '')
                <span class="text-indigo-300">(hasil pencarian seluruh data)</span>
            @endif
        </p>
        <span class="chip text-slate-300">Sumber : Cache</span>
    </div>

    @if(!empty($meta['message']))
        <div class="mb-5 rounded-xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-200">
            <i class="fa-solid fa-circle-info mr-2" aria-hidden="true"></i>
            {{ $meta['message'] }}
            <span class="block mt-1 text-xs text-amber-100/80">
                Petunjuk Google Maps tetap dapat dibuka melalui pencarian nama dan alamat Puskesmas.
            </span>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
        @forelse($puskesmasList as $puskesmas)
            <article class="glass relative flex min-w-0 flex-col justify-between overflow-hidden p-6 transition hover:border-white/20">
                @if($isRadiusSearch && $puskesmas['jarak_km'] !== null)
                    <div class="absolute right-0 top-0 rounded-bl-lg bg-indigo-600/90 px-3 py-1 text-xs font-bold text-white shadow">
                        {{ number_format($puskesmas['jarak_km'], 1) }} KM
                    </div>
                @endif

                <div>
                    <div class="mb-4 flex flex-wrap items-start justify-between gap-2">
                        @if(!empty($puskesmas['nama_kecamatan']) && $puskesmas['nama_kecamatan'] !== '-')
                            <span class="rounded-lg border border-aws/40 bg-aws/15 px-2.5 py-1 text-xs font-bold text-mist">
                                Kec. {{ $puskesmas['nama_kecamatan'] }}
                            </span>
                        @endif
                    </div>

                    <h2 class="break-words text-lg font-bold leading-snug text-white">
                        {{ $puskesmas['nama_puskesmas'] }}
                    </h2>
                    <p class="mt-1 text-xs font-semibold text-slate-400">
                        {{ $puskesmas['nama_kabupaten_kota'] }}
                    </p>

                    <div class="mt-4 border-t border-white/10 pt-4">
                        <p class="flex items-start gap-2.5 text-sm leading-relaxed text-slate-300">
                            <i class="fa-solid fa-location-dot mt-1 text-orchid" aria-hidden="true"></i>
                            <span class="min-w-0 break-words">{{ $puskesmas['alamat'] }}</span>
                        </p>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ $puskesmas['maps_url'] }}" target="_blank" rel="noopener noreferrer"
                       class="btn-ghost flex w-full items-center justify-center gap-2 border-indigo-500/30 text-indigo-300 hover:border-indigo-500/70 hover:bg-indigo-500/10">
                        <i class="fa-solid fa-diamond-turn-right text-indigo-400" aria-hidden="true"></i>
                        Cari Rute di Google Maps
                    </a>
                </div>
            </article>
        @empty
            <div class="glass col-span-full p-12 text-center">
                <i class="fa-solid fa-hospital-user mb-3 text-4xl text-slate-600" aria-hidden="true"></i>
                <h2 class="font-bold text-slate-100">
                    @if($isRadiusSearch)
                        Tidak ada data dengan koordinat yang dapat dihitung dalam radius 20 KM.
                    @else
                        Data Puskesmas Tidak Ditemukan.
                    @endif
                </h2>
                <p class="mx-auto mt-1 max-w-md text-xs text-slate-400">
                    Coba ubah kata kunci atau pilih Kabupaten/Kota lain. Pastikan izin lokasi dan konfigurasi Google Geocoding sudah tersedia untuk pencarian radius.
                </p>
                <a href="{{ route('puskesmas.index') }}" class="btn-ghost mt-5 inline-flex">Atur ulang pencarian</a>
            </div>
        @endforelse
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('searchForm');
    const county = document.getElementById('kabupaten');
    const search = document.getElementById('q');
    const locationText = document.getElementById('lokasi-text');
    const locationHelp = document.getElementById('location-help');

    if (!form || !county || !search) return;

    // Ganti kabupaten atau kata kunci = pencarian seluruh data JSON,
    // bukan pencarian radius dari koordinat GPS sebelumnya.
    county.addEventListener('change', () => {
        form.requestSubmit();
    });

    form.addEventListener('submit', () => {
        // Form tidak mengirim koordinat GPS; backend menerima filter global.
    });

    const params = new URLSearchParams(window.location.search);
    const hasCountyFilter = (params.get('kabupaten') || 'SEMUA').toUpperCase() !== 'SEMUA';
    const hasSearchFilter = (params.get('q') || '').trim() !== '';
    const hasGpsResult = params.has('lat') && params.has('lng');

    // Jangan meminta GPS ketika pengguna sedang melakukan pencarian/filter.
    if (hasCountyFilter || hasSearchFilter) {
        if (locationHelp) {
            locationHelp.textContent = 'Filter Kabupaten/Kota dan pencarian menampilkan seluruh data JSON, tanpa batasan radius.';
        }
        return;
    }

    // Koordinat sudah ada di URL: halaman sudah selesai menjalankan pencarian radius.
    if (hasGpsResult) {
        if (locationText) locationText.textContent = '- Lokasi terdeteksi';
        if (locationHelp) locationHelp.textContent = 'Menampilkan Puskesmas yang memiliki kandidat koordinat dalam radius 0–20 KM.';
        return;
    }

    // Otomatis meminta lokasi satu kali ketika direktori dibuka tanpa filter.
    if (!navigator.geolocation) {
        if (locationHelp) locationHelp.textContent = 'Browser tidak mendukung deteksi lokasi. Anda tetap dapat mencari berdasarkan nama atau Kabupaten/Kota.';
        return;
    }

    if (locationHelp) locationHelp.textContent = 'Meminta izin lokasi perangkat untuk mencari Puskesmas terdekat...';
    if (locationText) locationText.textContent = '- Meminta lokasi...';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const next = new URL(window.location.href);
            next.searchParams.set('lat', position.coords.latitude);
            next.searchParams.set('lng', position.coords.longitude);
            window.location.replace(next.toString());
        },
        (error) => {
            if (locationText) locationText.textContent = '- Lokasi tidak diizinkan';
            if (locationHelp) {
                locationHelp.textContent = error.code === 1
                    ? 'Izin lokasi tidak diberikan. Direktori tetap bisa digunakan melalui pencarian atau filter Kabupaten/Kota.'
                    : 'Lokasi tidak berhasil dideteksi. Direktori tetap bisa digunakan melalui pencarian atau filter Kabupaten/Kota.';
            }
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
    );
})();
</script>
@endpush
