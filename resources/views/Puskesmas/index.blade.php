@extends('layouts.app')


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
@endphp

<section class="py-8 bg-gradient-to-b from-white to-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-xs font-semibold mb-2">
                    <i class="fa-solid fa-hospital"></i> Direktori Fasilitas Kesehatan
                </div>
                <h1 class="text-3xl font-serif font-bold text-slate-900">Daftar & Alamat Puskesmas Jawa Barat</h1>
                <p class="text-slate-600 text-sm mt-1">Cari nama puskesmas, lokasi kecamatan, dan buka langsung di Google Maps.</p>
            </div>

            <!-- FORM FILTER & PENCARIAN -->
            <form method="GET" action="{{ route('puskesmas.index') }}" class="mt-4 md:mt-0 flex flex-col sm:flex-row gap-3">
                <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Cari Puskesmas / Kecamatan..." class="bg-white border border-slate-300 text-slate-800 text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 font-medium">

                <select name="kabupaten" onchange="this.form.submit()" class="bg-white border border-slate-300 text-slate-800 text-sm rounded-xl px-4 py-2.5 font-semibold focus:ring-2 focus:ring-emerald-500">
                    <option value="SEMUA" {{ $selectedKabupaten == 'SEMUA' ? 'selected' : '' }}>-- Semua Kab/Kota --</option>
                    @foreach($kabOptions as $kab)
                        <option value="{{ $kab }}" {{ $selectedKabupaten == $kab ? 'selected' : '' }}>{{ $kab }}</option>
                    @endforeach
                </select>

                <button type="submit" class="bg-emerald-600 text-white font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-emerald-700 transition-colors">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i> Cari
                </button>
            </form>
        </div>
    </div>
</section>

<section class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- STATUS SUMBER DATA & CATATAN KUALITAS -->
    @include('partials.data-status', ['meta' => $meta])

    <div class="flex items-center justify-between mb-6">
        <span class="text-sm font-semibold text-slate-600">
            Menampilkan <strong class="text-slate-900">{{ count($data['items']) }}</strong> Puskesmas ditemukan
            @if(($meta['total_source_records'] ?? 0) > count($data['items']))
                <span class="text-slate-400 font-normal">dari {{ $meta['total_source_records'] }} record valid</span>
            @endif
        </span>
        <span class="text-xs text-slate-400 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
            Sumber: {{ $sourceLabel }}@if($meta['source'] === 'LIVE_REST_API' && $meta['latency'] !== null) ({{ $meta['latency'] }} ms)@endif
        </span>
    </div>

    <!-- GRID CARD PUSKESMAS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($data['items'] as $puskesmas)
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        @if($puskesmas['nama_kecamatan'])
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-lg uppercase">
                                Kec. {{ $puskesmas['nama_kecamatan'] }}
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-slate-50 text-slate-500 border border-slate-200 text-xs font-semibold rounded-lg">
                                Kecamatan tidak tersedia
                            </span>
                        @endif
                        <span class="text-xs font-mono text-slate-400">{{ $puskesmas['kode_puskesmas'] ?? 'Kode tidak tersedia' }}</span>
                    </div>

                    <h3 class="font-bold text-lg text-slate-900 leading-snug">{{ $puskesmas['nama_puskesmas'] }}</h3>
                    <p class="text-xs font-semibold text-slate-500 uppercase mt-1">{{ $puskesmas['nama_kabupaten_kota'] }}</p>

                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <p class="text-xs text-slate-600 leading-relaxed flex items-start gap-2">
                            <i class="fa-solid fa-location-dot text-rose-500 mt-0.5"></i>
                            @if($puskesmas['alamat'])
                                <span>{{ $puskesmas['alamat'] }}</span>
                            @else
                                <span class="italic text-slate-400">Alamat tidak tersedia di sumber data</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ $puskesmas['maps_url'] }}" target="_blank" rel="noopener noreferrer"
                       title="Membuka pencarian Google Maps berdasarkan nama dan wilayah. Ini bukan verifikasi alamat."
                       class="w-full inline-flex items-center justify-center gap-2 bg-slate-900 text-white hover:bg-emerald-600 text-xs font-bold py-2.5 px-4 rounded-xl transition-all">
                        <i class="fa-solid fa-map-location-dot text-emerald-400"></i> Buka Petunjuk Google Maps
                    </a>
                </div>
            </div>
        @empty
            @if($meta['source'] === 'UNAVAILABLE')
                <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <i class="fa-solid fa-plug-circle-exclamation text-4xl text-slate-300 mb-3"></i>
                    <h3 class="font-bold text-slate-700">Daftar Puskesmas belum dapat dimuat</h3>
                    <p class="text-slate-500 text-xs mt-1">Sumber data tidak dapat diakses. Lihat keterangan di bagian atas halaman, lalu muat ulang beberapa saat lagi.</p>
                </div>
            @else
                <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <i class="fa-solid fa-hospital-user text-4xl text-slate-300 mb-3"></i>
                    <h3 class="font-bold text-slate-700">Data Puskesmas Tidak Ditemukan</h3>
                    <p class="text-slate-500 text-xs mt-1">Coba ubah kata kunci pencarian atau pilih kabupaten/kota yang lain.</p>
                </div>
            @endif
        @endforelse
    </div>

    <p class="mt-6 text-xs text-slate-400">
        Tautan Google Maps adalah pencarian berdasarkan nama dan wilayah dari data sumber. Tautan itu tidak membuktikan bahwa alamat sudah diverifikasi.
    </p>
</section>
@endsection