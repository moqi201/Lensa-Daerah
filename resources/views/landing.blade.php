@extends('layouts.app')

@section('title', 'LENSA DAERAH - Data Puskesmas Jawa Barat yang Terbuka dan Bisa Diperiksa')

@section('content')
@php
    // Semua visual di halaman ini adalah ILUSTRASI tampilan (tanpa angka data).
    // Angka sebenarnya hanya tampil di Dashboard, langsung dari API.
    $heroBars  = [38, 52, 44, 70, 58, 88, 64, 48, 76, 56, 42, 62];
    $miniBars  = [45, 70, 55, 90, 62, 78, 50, 66];

    $features = [
        ['icon' => 'fa-signal',            'title' => 'Status sumber terlihat',     'text' => 'Setiap halaman menandai apakah data berasal dari API langsung, cache, cache kedaluwarsa, atau sedang tidak tersedia.'],
        ['icon' => 'fa-clipboard-check',   'title' => 'Kualitas data dicatat',      'text' => 'Klasifikasi dan catatan kualitas menjelaskan field yang kosong atau kelengkapan yang belum diverifikasi.'],
        ['icon' => 'fa-clock-rotate-left', 'title' => 'Waktu pengambilan tercatat', 'text' => 'Pengguna dapat melihat kapan data terakhir berhasil diambil dari API sumber.'],
        ['icon' => 'fa-terminal',          'title' => 'Bisa diperiksa langsung',    'text' => 'API Inspector menampilkan endpoint, latensi, dan contoh hasil olahan backend untuk dicocokkan dengan sumber.'],
    ];

    $sourceStates = [
        ['dot' => 'bg-emerald-400', 'label' => 'API langsung',      'text' => 'Data baru saja diambil dari REST API. Latensi permintaan ikut ditampilkan.'],
        ['dot' => 'bg-slate-400',   'label' => 'Cache',             'text' => 'Salinan yang masih berlaku. Waktu pengambilan terakhir dicantumkan.'],
        ['dot' => 'bg-amber-400',   'label' => 'Cache kedaluwarsa', 'text' => 'API tidak dapat diakses, sehingga memakai salinan terakhir yang mungkin sudah tidak terkini.'],
        ['dot' => 'bg-rose-400',    'label' => 'Tidak tersedia',    'text' => 'Tidak ada salinan valid, jadi tidak ada data yang ditampilkan.'],
    ];

    $qualityLabels = [
        ['Valid dan lengkap', 'border-emerald-400/30 bg-emerald-500/10 text-emerald-300'],
        ['Valid, sebagian field kosong', 'border-emerald-400/30 bg-emerald-500/10 text-emerald-300'],
        ['Kelengkapan belum diverifikasi', 'border-amber-400/30 bg-amber-500/10 text-amber-300'],
        ['Data tidak valid', 'border-rose-400/30 bg-rose-500/10 text-rose-300'],
        ['Data tidak tersedia', 'border-rose-400/30 bg-rose-500/10 text-rose-300'],
    ];

    $benefits = [
        ['icon' => 'fa-users',          'title' => 'Warga',                 'text' => 'Mencari Puskesmas terdekat dan membuka petunjuk lokasinya tanpa mengurus data mentah.'],
        ['icon' => 'fa-graduation-cap', 'title' => 'Peneliti dan mahasiswa', 'text' => 'Membandingkan sebaran fasilitas antarwilayah dengan keterbatasan data yang dijelaskan terang.'],
        ['icon' => 'fa-newspaper',      'title' => 'Jurnalis',              'text' => 'Mengutip angka yang bisa ditelusuri ke dataset beserta waktu dan status pengambilannya.'],
        ['icon' => 'fa-building-columns', 'title' => 'Pemerintah daerah',   'text' => 'Melihat kualitas data sendiri dan bagian mana yang perlu dilengkapi pada sumber.'],
    ];
@endphp

<!-- HERO -->
<section class="relative" aria-labelledby="hero-title">
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 pb-16 pt-12 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:gap-14 lg:px-8 lg:pb-24 lg:pt-20">
        <div>
            <span class="chip"><i class="fa-solid fa-landmark text-mist" aria-hidden="true"></i> Portal Open Data Jawa Barat</span>
            <h1 id="hero-title" class="mt-5 text-4xl font-extrabold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-6xl">
                Data Puskesmas Jawa Barat, terbuka dan bisa diperiksa
            </h1>
            <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">
                LENSA DAERAH mengolah REST API Open Data Jawa Barat menjadi dashboard dan direktori Puskesmas. Setiap angka disertai status sumber dan catatan kualitas datanya.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('dashboard') }}" class="btn-primary px-6">
                    <i class="fa-solid fa-chart-pie" aria-hidden="true"></i> Buka dashboard
                </a>
                <a href="{{ route('puskesmas.index') }}" class="btn-ghost px-6">
                    <i class="fa-solid fa-magnifying-glass-location text-mist" aria-hidden="true"></i> Cari Puskesmas
                </a>
            </div>

            <ul class="mt-8 space-y-2.5 text-sm text-slate-300">
                <li class="flex items-center gap-2.5"><i class="fa-solid fa-circle-check text-emerald-400" aria-hidden="true"></i> Bersumber dari REST API Open Data Jawa Barat</li>
                <li class="flex items-center gap-2.5"><i class="fa-solid fa-circle-check text-emerald-400" aria-hidden="true"></i> Status dan kualitas data selalu ditampilkan</li>
                <li class="flex items-center gap-2.5"><i class="fa-solid fa-circle-check text-emerald-400" aria-hidden="true"></i> Tautan Google Maps untuk setiap Puskesmas</li>
            </ul>
        </div>

        <!-- Ilustrasi pratinjau dashboard -->
        <figure class="glass p-5 sm:p-6">
            <div aria-hidden="true">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-white">Jumlah Puskesmas per wilayah</p>
                    <span class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-xs font-semibold text-slate-300">Semua Kab/Kota</span>
                </div>

                <div class="mt-5 flex h-40 items-end gap-1.5 sm:gap-2">
                    @foreach($heroBars as $h)
                        <span class="flex-1 rounded-t-lg bg-gradient-to-t from-aws to-amethyst {{ $h > 80 ? '' : 'opacity-70' }}" style="height: {{ $h }}%"></span>
                    @endforeach
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                        <svg viewBox="0 0 36 36" class="h-16 w-16 shrink-0 -rotate-90" fill="none">
                            <circle cx="18" cy="18" r="15.9155" stroke="rgba(255,255,255,.08)" stroke-width="4"/>
                            <circle cx="18" cy="18" r="15.9155" stroke="#0073BB" stroke-width="4" stroke-dasharray="34 66" stroke-dashoffset="0"/>
                            <circle cx="18" cy="18" r="15.9155" stroke="#8C43FC" stroke-width="4" stroke-dasharray="22 78" stroke-dashoffset="-34"/>
                            <circle cx="18" cy="18" r="15.9155" stroke="#A166FF" stroke-width="4" stroke-dasharray="14 86" stroke-dashoffset="-56"/>
                        </svg>
                        <div class="text-xs leading-relaxed text-slate-300">
                            <p class="font-semibold text-white">Proporsi wilayah</p>
                            <p class="text-slate-400">Delapan terbesar dan sisanya</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-xs font-semibold text-white">Status data</p>
                        <ul class="mt-2 space-y-1.5 text-xs text-slate-300">
                            <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> API langsung</li>
                            <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-slate-400"></span> Cache</li>
                            <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Cache kedaluwarsa</li>
                        </ul>
                    </div>
                </div>
            </div>
            <figcaption class="mt-4 text-xs text-slate-400">Ilustrasi tampilan dashboard. Angka sebenarnya ditampilkan di halaman Dashboard.</figcaption>
        </figure>
    </div>
</section>

<!-- FITUR TRANSPARANSI DATA PUBLIK -->
<section id="fitur" class="border-t border-white/10 py-16 lg:py-20" aria-labelledby="fitur-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <h2 id="fitur-title" class="section-title">Transparansi data publik, termasuk keterbatasannya</h2>
            <p class="mt-3 text-slate-300">Data ditampilkan apa adanya dari sumber. Bagian yang kosong, belum diverifikasi, atau sudah lama tidak diperbarui tetap diberi tahu.</p>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($features as $feature)
                <div class="glass p-6">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-aws/30 to-amethyst/30 text-lilac ring-1 ring-white/10">
                        <i class="fa-solid {{ $feature['icon'] }}" aria-hidden="true"></i>
                    </span>
                    <h3 class="mt-5 text-base font-bold text-white">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $feature['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- VISUALISASI JUMLAH PUSKESMAS -->
<section id="visualisasi" class="border-t border-white/10 py-16 lg:py-20" aria-labelledby="visual-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="gap-8 md:flex md:items-end md:justify-between">
            <div class="max-w-2xl">
                <h2 id="visual-title" class="section-title">Jumlah Puskesmas per wilayah dalam satu dashboard</h2>
                <p class="mt-3 text-slate-300">Pilih kabupaten/kota, lalu baca sebaran dan proporsinya lewat grafik dan tabel.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn-ghost mt-5 shrink-0 md:mt-0">Lihat dashboard</a>
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-3">
            <div class="glass p-6">
                <div class="flex h-32 items-end gap-1.5" aria-hidden="true">
                    @foreach($miniBars as $h)
                        <span class="flex-1 rounded-t-md bg-gradient-to-t from-aws to-amethyst" style="height: {{ $h }}%"></span>
                    @endforeach
                </div>
                <h3 class="mt-5 text-base font-bold text-white">Grafik batang</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">Membandingkan jumlah Puskesmas antarwilayah. Di layar kecil, grafik dapat digeser ke samping.</p>
            </div>

            <div class="glass p-6">
                <div class="flex h-32 items-center justify-center" aria-hidden="true">
                    <svg viewBox="0 0 36 36" class="h-28 w-28 -rotate-90" fill="none">
                        <circle cx="18" cy="18" r="15.9155" stroke="rgba(255,255,255,.08)" stroke-width="4.5"/>
                        <circle cx="18" cy="18" r="15.9155" stroke="#0073BB" stroke-width="4.5" stroke-dasharray="30 70" stroke-dashoffset="0"/>
                        <circle cx="18" cy="18" r="15.9155" stroke="#8C43FC" stroke-width="4.5" stroke-dasharray="24 76" stroke-dashoffset="-30"/>
                        <circle cx="18" cy="18" r="15.9155" stroke="#A166FF" stroke-width="4.5" stroke-dasharray="16 84" stroke-dashoffset="-54"/>
                        <circle cx="18" cy="18" r="15.9155" stroke="#7CC4F0" stroke-width="4.5" stroke-dasharray="10 90" stroke-dashoffset="-70"/>
                    </svg>
                </div>
                <h3 class="mt-5 text-base font-bold text-white">Grafik donat</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">Menunjukkan proporsi delapan wilayah terbesar dan sisanya dalam satu gambar.</p>
            </div>

            <div class="glass p-6">
                <div class="flex h-32 flex-col justify-center gap-2.5" aria-hidden="true">
                    @foreach([70, 52, 84, 60] as $w)
                        <div class="flex items-center gap-3">
                            <span class="h-2.5 w-6 rounded-full bg-white/15"></span>
                            <span class="h-2.5 rounded-full bg-white/10" style="width: {{ $w }}%"></span>
                            <span class="ml-auto h-2.5 w-8 rounded-full bg-aws/60"></span>
                        </div>
                    @endforeach
                </div>
                <h3 class="mt-5 text-base font-bold text-white">Tabel wilayah</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">Rincian per wilayah beserta porsi dari total, dengan pencarian berdasarkan nama.</p>
            </div>
        </div>
    </div>
</section>

<!-- DIREKTORI, PENCARIAN LOKASI & GOOGLE MAPS -->
<section id="direktori" class="border-t border-white/10 py-16 lg:py-20" aria-labelledby="direktori-title">
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
        <div>
            <h2 id="direktori-title" class="section-title">Direktori Puskesmas Jawa Barat</h2>
            <p class="mt-3 max-w-xl text-slate-300">Cari berdasarkan nama Puskesmas atau kecamatan, saring per kabupaten/kota, lalu buka petunjuknya di Google Maps.</p>

            <form method="GET" action="{{ route('puskesmas.index') }}" role="search" class="mt-6 flex max-w-xl flex-col gap-3 sm:flex-row">
                <label for="landing-q" class="sr-only">Cari Puskesmas atau kecamatan</label>
                <input type="search" id="landing-q" name="q" autocomplete="off" placeholder="Cari Puskesmas / Kecamatan..." class="field flex-1">
                <button type="submit" class="btn-primary shrink-0">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Cari
                </button>
            </form>

            <p class="mt-4 max-w-xl text-xs leading-relaxed text-slate-500">
                Tautan Google Maps adalah pencarian berdasarkan nama dan wilayah dari data sumber. Tautan itu tidak membuktikan bahwa alamat sudah diverifikasi.
            </p>
        </div>

        <!-- Ilustrasi kartu Puskesmas -->
        <div aria-hidden="true" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
            @foreach([0, 1] as $i)
                <div class="glass p-5 {{ $i === 1 ? 'sm:translate-y-6 lg:translate-y-0 xl:translate-y-6' : '' }}">
                    <div class="flex items-center justify-between">
                        <span class="rounded-lg border border-aws/40 bg-aws/15 px-2.5 py-1 text-xs font-bold text-mist">Kec. ...</span>
                        <span class="h-2.5 w-16 rounded-full bg-white/10"></span>
                    </div>
                    <span class="mt-4 block h-3.5 w-3/4 rounded-full bg-white/20"></span>
                    <span class="mt-2 block h-2.5 w-1/3 rounded-full bg-white/10"></span>
                    <div class="mt-4 flex gap-2 border-t border-white/10 pt-4">
                        <i class="fa-solid fa-location-dot mt-0.5 text-orchid"></i>
                        <div class="flex-1 space-y-2">
                            <span class="block h-2.5 w-full rounded-full bg-white/10"></span>
                            <span class="block h-2.5 w-2/3 rounded-full bg-white/10"></span>
                        </div>
                    </div>
                    <div class="btn-ghost mt-5 w-full text-xs">
                        <i class="fa-solid fa-map-location-dot text-mist"></i> Buka Petunjuk Google Maps
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- INFORMASI SUMBER DAN KUALITAS DATA -->
<section id="sumber" class="border-t border-white/10 py-16 lg:py-20" aria-labelledby="sumber-title">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[.9fr_1.1fr] lg:gap-14 lg:px-8">
        <div>
            <h2 id="sumber-title" class="section-title">Dari mana data berasal, dan seberapa lengkap</h2>
            <p class="mt-3 max-w-md text-slate-300">
                Portal ini tidak menyembunyikan masalah pada sumber. Jika API tidak dapat diakses, pengguna diberi tahu apakah yang tampil salinan lama atau tidak ada data sama sekali.
            </p>

            <h3 class="mt-8 text-sm font-bold text-white">Klasifikasi kualitas data</h3>
            <ul class="mt-3 flex flex-wrap gap-2">
                @foreach($qualityLabels as [$label, $classes])
                    <li class="rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $classes }}">{{ $label }}</li>
                @endforeach
            </ul>
        </div>

        <div class="glass divide-y divide-white/10">
            @foreach($sourceStates as $state)
                <div class="flex items-start gap-4 p-5">
                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $state['dot'] }}" aria-hidden="true"></span>
                    <div>
                        <h3 class="text-sm font-bold text-white">{{ $state['label'] }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-slate-400">{{ $state['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- MANFAAT -->
<section id="manfaat" class="border-t border-white/10 py-16 lg:py-20" aria-labelledby="manfaat-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 id="manfaat-title" class="section-title max-w-2xl">Manfaat bagi yang membutuhkan data fasilitas kesehatan</h2>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($benefits as $benefit)
                <div class="glass p-6">
                    <i class="fa-solid {{ $benefit['icon'] }} text-xl text-lilac" aria-hidden="true"></i>
                    <h3 class="mt-4 text-base font-bold text-white">{{ $benefit['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $benefit['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- AJAKAN BERTINDAK -->
<section class="border-t border-white/10 py-16 lg:py-20" aria-labelledby="cta-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-electric/40 via-iris/40 to-amethyst/30 p-8 text-center shadow-glass sm:p-12">
            <h2 id="cta-title" class="mx-auto max-w-2xl text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Periksa datanya sendiri</h2>
            <p class="mx-auto mt-3 max-w-xl text-slate-200">Buka dashboard untuk melihat angka per wilayah, atau cari Puskesmas di sekitar Anda lewat direktori.</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('dashboard') }}" class="btn-primary px-6 ring-1 ring-white/20">Buka dashboard</a>
                <a href="{{ route('puskesmas.index') }}" class="btn-ghost px-6">Buka direktori</a>
            </div>
        </div>
    </div>
</section>
@endsection