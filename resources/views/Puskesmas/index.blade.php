@extends('layouts.app')

@section('title', 'Direktori Puskesmas Jawa Barat - LENSA DAERAH')

@section('content')
@php
    $meta = $data['meta'];

    // Opsi dropdown diambil dari nilai sumber; bila belum ada data, pakai daftar acuan.
    $kabOptions = !empty($meta['kabupaten_options'])
        ? $meta['kabupaten_options']
        : config('puskesmas.reference_regions');

    $sourceLabel = [
        'LIVE_REST_API' => 'API langsung',
        'CACHE'         => 'Cache',
        'CACHE_STALE'   => 'Cache kedaluwarsa',
        'UNAVAILABLE'   => 'Tidak tersedia',
    ][$meta['source']] ?? $meta['source'];

    // LOGIKA PEMBATASAN CARD:
    // Cek apakah dropdown bernilai 'SEMUA' dan kolom pencarian kosong
    $isDefaultView = ($selectedKabupaten === 'SEMUA' && empty($searchQuery));
    
    // Jika tampilan awal, batasi 8. Jika difilter/dicari, tampilkan semua.
    $puskesmasList = $isDefaultView ? array_slice($data['items'], 0, 8) : $data['items'];
@endphp

<!-- JUDUL & FORM PENCARIAN -->
<section class="border-b border-white/10" aria-labelledby="directory-title">
    <div class="mx-auto max-w-7xl px-4 pb-10 pt-10 sm:px-6 lg:px-8 lg:pt-14">
        <span class="chip"><i class="fa-solid fa-hospital text-mist" aria-hidden="true"></i> Direktori Fasilitas Kesehatan</span>
        <h1 id="directory-title" class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-4xl">
            Daftar & Alamat Puskesmas Jawa Barat
        </h1>
        <p class="mt-3 max-w-2xl text-base leading-relaxed text-slate-300">
            Cari nama puskesmas, lokasi kecamatan, dan buka langsung di Google Maps.
        </p>

        <!-- FORM FILTER & PENCARIAN -->
        <form method="GET" action="{{ route('puskesmas.index') }}" role="search"
              class="glass mt-8 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[1fr_17rem_auto]">
            <div>
                <label for="q" class="sr-only">Cari Puskesmas atau kecamatan</label>
                <input type="search" id="q" name="q" value="{{ $searchQuery }}" autocomplete="off"
                       placeholder="Cari Puskesmas / Kecamatan..." class="field font-medium">
            </div>

            <div>
                <label for="kabupaten" class="sr-only">Filter kabupaten atau kota</label>
                <select id="kabupaten" name="kabupaten" onchange="this.form.submit()" class="field font-semibold">
                    <option value="SEMUA" {{ $selectedKabupaten == 'SEMUA' ? 'selected' : '' }}>Semua Kab/Kota</option>
                    @foreach($kabOptions as $kab)
                        <option value="{{ $kab }}" {{ $selectedKabupaten == $kab ? 'selected' : '' }}>{{ $kab }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-primary sm:col-span-2 lg:col-span-1">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Cari
            </button>
        </form>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" aria-label="Hasil pencarian Puskesmas">
    <!-- STATUS SUMBER DATA & CATATAN KUALITAS -->  
    @include('partials.data-status', ['meta' => $meta])

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-300" role="status">
            Menampilkan <strong class="text-white">{{ count($puskesmasList) }}</strong> Puskesmas ditemukan
            @if(($meta['total_source_records'] ?? 0) > count($puskesmasList))
                <span class="text-slate-500">dari {{ $meta['total_source_records'] }} record valid</span>
            @endif
        </p>
        <span class="chip text-slate-300">
            Sumber: {{ $sourceLabel }}@if($meta['source'] === 'LIVE_REST_API' && $meta['latency'] !== null) ({{ $meta['latency'] }} ms)@endif
        </span>
    </div>

    <!-- GRID CARD PUSKESMAS -->
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
        @forelse($puskesmasList as $puskesmas)
            <article class="glass flex min-w-0 flex-col justify-between p-6 transition hover:border-white/20">
                <div>
                    <div class="mb-4 flex items-start justify-between gap-3">
                        @if($puskesmas['nama_kecamatan'])
                            <span class="rounded-lg border border-aws/40 bg-aws/15 px-2.5 py-1 text-xs font-bold text-mist">
                                Kec. {{ $puskesmas['nama_kecamatan'] }}
                            </span>
                        @else
                            <span class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-xs font-semibold text-slate-400">
                                Kecamatan tidak tersedia
                            </span>
                        @endif
                        <span class="shrink-0 font-mono text-xs text-slate-500">{{ $puskesmas['kode_puskesmas'] ?? 'Kode tidak tersedia' }}</span>
                    </div>

                    <h2 class="break-words text-lg font-bold leading-snug text-white">{{ $puskesmas['nama_puskesmas'] }}</h2>
                    <p class="mt-1 text-xs font-semibold text-slate-400">{{ $puskesmas['nama_kabupaten_kota'] }}</p>

                    <div class="mt-4 border-t border-white/10 pt-4">
                        <p class="flex items-start gap-2.5 text-sm leading-relaxed text-slate-300">
                            <i class="fa-solid fa-location-dot mt-1 text-orchid" aria-hidden="true"></i>
                            @if($puskesmas['alamat'])
                                <span class="min-w-0 break-words">{{ $puskesmas['alamat'] }}</span>
                            @else
                                <span class="italic text-slate-500">Alamat tidak tersedia di sumber data</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ $puskesmas['maps_url'] }}" target="_blank" rel="noopener noreferrer"
                       title="Membuka pencarian Google Maps berdasarkan nama dan wilayah. Ini bukan verifikasi alamat."
                       class="btn-ghost w-full">
                        <i class="fa-solid fa-map-location-dot text-mist" aria-hidden="true"></i>
                        Buka Petunjuk Google Maps
                        <span class="sr-only">untuk {{ $puskesmas['nama_puskesmas'] }} (tab baru)</span>
                    </a>
                </div>
            </article>
        @empty
            @if($meta['source'] === 'UNAVAILABLE')
                <div class="glass col-span-full p-12 text-center">
                    <i class="fa-solid fa-plug-circle-exclamation mb-3 text-4xl text-slate-600" aria-hidden="true"></i>
                    <h2 class="font-bold text-slate-100">Daftar Puskesmas belum dapat dimuat</h2>
                    <p class="mx-auto mt-1 max-w-md text-xs text-slate-400">Sumber data tidak dapat diakses. Lihat keterangan di bagian atas halaman, lalu muat ulang beberapa saat lagi.</p>
                </div>
            @else
                <div class="glass col-span-full p-12 text-center">
                    <i class="fa-solid fa-hospital-user mb-3 text-4xl text-slate-600" aria-hidden="true"></i>
                    <h2 class="font-bold text-slate-100">Data Puskesmas Tidak Ditemukan</h2>
                    <p class="mx-auto mt-1 max-w-md text-xs text-slate-400">Coba ubah kata kunci pencarian atau pilih kabupaten/kota yang lain.</p>
                    <a href="{{ route('puskesmas.index') }}" class="btn-ghost mt-5">Atur ulang pencarian</a>
                </div>
            @endif
        @endforelse
    </div>

    <p class="mt-8 text-xs leading-relaxed text-slate-500">
        Tautan Google Maps adalah pencarian berdasarkan nama dan wilayah dari data sumber. Tautan itu tidak membuktikan bahwa alamat sudah diverifikasi.
    </p>
</section>
@endsection