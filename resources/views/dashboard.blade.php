@extends('layouts.app')

@section('title', 'Dashboard Transparansi - LENSA DAERAH')

@section('content')
@php
    $meta    = $data['meta'];
    $summary = $data['summary'];

    $kabOptions = !empty($meta['kabupaten_options'])
        ? $meta['kabupaten_options']
        : config('puskesmas.reference_regions');

    $sourceLabel = [
        'LIVE_REST_API' => 'API langsung',
        'CACHE'         => 'Cache',
        'CACHE_STALE'   => 'Cache kedaluwarsa',
        'UNAVAILABLE'   => 'Tidak tersedia',
    ][$meta['source']] ?? $meta['source'];

    $num      = fn ($n) => $n === null ? '—' : number_format($n, 0, ',', '.');
    $hasItems = count($data['items']) > 0;
    $top      = $summary['wilayah_terbanyak'] ?? null;

    // Semua angka berasal dari dataset ($summary), bukan dihitung ulang di tampilan.
    $stats = [
        [
            'label'    => 'Total Puskesmas',
            'icon'     => 'fa-hospital-user',
            'value'    => $num($summary['total_puskesmas']),
            'note'     => 'Jumlah dari dataset, wilayah yang dipilih',
            'featured' => true,
        ],
        [
            'label' => 'Jumlah Wilayah',
            'icon'  => 'fa-map-location-dot',
            'value' => $num($summary['total_wilayah']),
            'note'  => 'Kabupaten/Kota pada data',
        ],
        [
            'label' => 'Terbanyak',
            'icon'  => 'fa-chart-column',
            'value' => $top ? $num($top['jumlah_puskesmas']) : '—',
            'note'  => $top['nama_kabupaten_kota'] ?? 'Tidak ada data',
        ],
        [
            'label' => 'Periode Data',
            'icon'  => 'fa-calendar-days',
            'value' => $summary['tahun'] ?: 'Tidak dilaporkan',
            'note'  => $summary['tahun'] ? 'Tahun dataset yang ditampilkan' : 'Sumber tidak menyertakan tahun',
            'small' => !$summary['tahun'],
        ],
    ];
@endphp

<!-- HERO, FILTER, STATUS DATA, STATISTIK -->
<section class="border-b border-white/10" aria-labelledby="dashboard-title">
    <div class="mx-auto max-w-7xl px-4 pb-10 pt-10 sm:px-6 lg:px-8 lg:pt-14">
        <div class="gap-10 lg:flex lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <span class="chip"><i class="fa-solid fa-shield-halved text-mist" aria-hidden="true"></i> Kompetisi Web In Action (WIA)</span>
                <h1 id="dashboard-title" class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Transparansi Fasilitas Kesehatan & Data Publik Jawa Barat
                </h1>
                <p class="mt-4 max-w-2xl text-base leading-relaxed text-slate-300 sm:text-lg">
                    Platform pengolah REST API Open Data Jawa Barat. Menyajikan jumlah Puskesmas per Kabupaten & Kota apa adanya dari sumber, lengkap dengan status dan keterbatasan datanya.
                </p>
            </div>

            <!-- FILTER WILAYAH -->
            <div class="glass mt-8 w-full p-5 lg:mt-0 lg:max-w-sm">
                <form method="GET" action="{{ route('dashboard') }}">
                    <label for="kabupaten" class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-100">
                        <i class="fa-solid fa-map-location-dot text-mist" aria-hidden="true"></i> Pilih wilayah Kab / Kota
                    </label>
                    <select id="kabupaten" name="kabupaten" onchange="this.form.submit()" class="field font-semibold">
                        <option value="SEMUA" {{ $selectedKabupaten == 'SEMUA' ? 'selected' : '' }}>-- Semua Kab/Kota Jawa Barat --</option>
                        @foreach($kabOptions as $kab)
                            <option value="{{ $kab }}" {{ $selectedKabupaten == $kab ? 'selected' : '' }}>{{ $kab }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="btn-primary mt-3 w-full">Terapkan filter</button></noscript>
                </form>
                <div class="mt-4 flex items-center justify-between gap-3 border-t border-white/10 pt-3 text-xs text-slate-400">
                    <span><i class="fa-solid fa-database mr-1" aria-hidden="true"></i> Sumber: {{ $sourceLabel }}</span>
                    @if($meta['source'] === 'LIVE_REST_API' && $meta['latency'] !== null)
                        <span class="font-semibold text-emerald-300"><i class="fa-solid fa-bolt mr-1" aria-hidden="true"></i> {{ $meta['latency'] }} ms</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- STATUS SUMBER DATA & CATATAN KUALITAS -->
        <div class="mt-10">
            @include('partials.data-status', ['meta' => $meta])
        </div>

        <!-- KARTU STATISTIK -->
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($stats as $stat)
                @php $featured = !empty($stat['featured']); @endphp
                <div class="{{ $featured
                        ? 'rounded-3xl border border-white/10 bg-gradient-to-br from-electric via-iris to-amethyst p-5 shadow-lg shadow-amethyst/20'
                        : 'glass p-5' }}">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-sm font-semibold {{ $featured ? 'text-white/90' : 'text-slate-300' }}">{{ $stat['label'] }}</dt>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $featured ? 'bg-white/20 text-white' : 'bg-white/5 text-mist' }}">
                            <i class="fa-solid {{ $stat['icon'] }} text-lg" aria-hidden="true"></i>
                        </span>
                    </div>
                    <dd class="mt-4">
                        <span class="block break-words font-extrabold text-white {{ !empty($stat['small']) ? 'text-xl text-slate-300' : 'text-3xl' }}">{{ $stat['value'] }}</span>
                        <span class="mt-1 block text-xs {{ $featured ? 'text-white/90' : 'text-slate-400' }}">{{ $stat['note'] }}</span>
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>

<!-- GRAFIK -->
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" aria-label="Grafik jumlah Puskesmas">
    @if($hasItems)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="glass min-w-0 p-5 sm:p-6 lg:col-span-2">
                <h2 class="text-base font-bold text-white">Sebaran Puskesmas per Wilayah</h2>
                <p class="mt-1 text-xs text-slate-400 sm:hidden">Geser ke samping untuk melihat semua wilayah.</p>
                <div class="mt-4 overflow-x-auto">
                    <div id="bar-wrap" class="h-80">
                        <canvas id="barChart" role="img" aria-label="Grafik batang jumlah Puskesmas per wilayah. Rinciannya ada di tabel di bawah."></canvas>
                    </div>
                </div>
            </div>

            <div class="glass min-w-0 p-5 sm:p-6">
                <h2 class="text-base font-bold text-white">Proporsi Puskesmas per Wilayah</h2>
                <div class="mt-4 flex h-80 items-center justify-center">
                    <canvas id="donutChart" role="img" aria-label="Grafik donat proporsi Puskesmas di delapan wilayah terbesar dan wilayah lainnya."></canvas>
                </div>
            </div>
        </div>
    @else
        <div class="glass p-10 text-center">
            <i class="fa-solid fa-chart-column mb-3 text-4xl text-slate-600" aria-hidden="true"></i>
            <h2 class="font-bold text-slate-100">Grafik belum dapat ditampilkan</h2>
            <p class="mx-auto mt-1 max-w-md text-xs text-slate-400">Tidak ada data untuk wilayah ini atau sumber data sedang tidak tersedia. Lihat status data di bagian atas halaman.</p>
        </div>
    @endif
</section>

<!-- TABEL DATA WILAYAH -->
<section class="border-t border-white/10 py-10" aria-labelledby="table-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 gap-4 md:flex md:items-end md:justify-between">
            <div>
                <h2 id="table-title" class="section-title">Rincian Data Wilayah Jawa Barat</h2>
                <p class="mt-1 text-sm text-slate-400">Menampilkan {{ count($data['items']) }} wilayah dari dataset jumlah Puskesmas</p>
            </div>
            <div class="mt-4 w-full md:mt-0 md:w-72">
                <label for="search-input" class="sr-only">Cari nama kabupaten/kota</label>
                <input type="search" id="search-input" autocomplete="off" placeholder="Cari nama kabupaten/kota..." class="field">
            </div>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-white/10 bg-white/[0.03]">
            <table class="w-full min-w-[34rem] text-left text-sm">
                <caption class="sr-only">Jumlah Puskesmas per kabupaten/kota</caption>
                <thead class="border-b border-white/10 bg-white/5 text-xs text-slate-300">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 font-semibold">No</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Kabupaten / Kota</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Jumlah Puskesmas</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Tahun</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Porsi dari total</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y divide-white/5">
                    @forelse($data['items'] as $index => $item)
                        <tr data-row class="transition-colors hover:bg-white/[0.04]">
                            <td class="px-5 py-3.5 font-bold text-slate-100">{{ $index + 1 }}</td>
                            <td class="px-5 py-3.5 font-semibold text-slate-100">{{ $item['nama_kabupaten_kota'] }}</td>
                            <td class="px-5 py-3.5 font-semibold text-mist">{{ $item['jumlah_puskesmas'] }} Unit</td>
                            <td class="px-5 py-3.5 text-slate-300">{{ $item['tahun'] ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-300">
                                @if(($summary['total_puskesmas'] ?? 0) > 0)
                                    {{ number_format($item['jumlah_puskesmas'] / $summary['total_puskesmas'] * 100, 1, ',', '.') }}%
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                {{ $meta['source'] === 'UNAVAILABLE' ? 'Data sementara tidak tersedia.' : 'Data tidak ditemukan.' }}
                            </td>
                        </tr>
                    @endforelse
                    <tr id="table-empty" class="hidden">
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">Tidak ada wilayah yang cocok dengan kata kunci.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p id="table-count" class="sr-only" role="status" aria-live="polite"></p>
    </div>
</section>

<!-- API INSPECTOR -->
<section class="border-t border-white/10 py-12" aria-labelledby="inspector-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="chip"><i class="fa-solid fa-terminal text-mist" aria-hidden="true"></i> Verifikasi langsung</span>
                <h2 id="inspector-title" class="mt-3 text-xl font-extrabold tracking-tight text-white">Laravel API Proxy Inspector</h2>
            </div>
            <span class="rounded-lg border border-white/10 bg-white/5 px-3 py-1 font-mono text-xs text-slate-300">
                {{ $meta['source'] }}@if(!empty($meta['error_code'])) / {{ $meta['error_code'] }}@endif
            </span>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-white/10 bg-ink-950/80 p-4 font-mono text-xs leading-relaxed shadow-glass sm:p-5">
            <div class="mb-3 flex flex-wrap justify-between gap-2 border-b border-white/10 pb-3 text-slate-400">
                <span>Endpoint: <span class="break-all text-emerald-300">{{ $meta['endpoint'] }}</span></span>
                @if($meta['source'] === 'LIVE_REST_API' && $meta['latency'] !== null)
                    <span class="text-amber-300">Latency: {{ $meta['latency'] }} ms</span>
                @endif
            </div>
            <p class="mb-2 text-slate-400">Contoh 3 baris pertama setelah diproses oleh backend (bukan respons mentah API).</p>
            <pre tabindex="0" class="max-h-48 overflow-auto text-slate-300">{{ json_encode(array_slice($data['items'], 0, 3), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const itemsData = @json($data['items']);
    const shortName = (name) => name.replace(/^KABUPATEN /i, 'KAB. ');

    // Warna grafik mengikuti tema gelap
    const tone = { text: '#94A3B8', legend: '#CBD5E1', grid: 'rgba(255,255,255,.07)', surface: '#1C2836' };

    if (window.Chart && itemsData.length > 0 && document.getElementById('barChart')) {
        Chart.defaults.font.family = '"Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif';
        Chart.defaults.color = tone.text;

        const tooltip = {
            backgroundColor: '#232F3E', borderColor: 'rgba(255,255,255,.12)', borderWidth: 1,
            titleColor: '#F1F5F9', bodyColor: '#CBD5E1', padding: 10, cornerRadius: 10,
        };

        // Lebar grafik batang mengikuti jumlah wilayah; di layar kecil area ini bisa digeser.
        document.getElementById('bar-wrap').style.minWidth = Math.max(560, itemsData.length * 26) + 'px';

        // Bar chart: jumlah Puskesmas per wilayah
        const barCtx = document.getElementById('barChart').getContext('2d');
        const barFill = barCtx.createLinearGradient(0, 0, 0, 320);
        barFill.addColorStop(0, '#8C43FC');
        barFill.addColorStop(1, '#0073BB');

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: itemsData.map(i => shortName(i.nama_kabupaten_kota)),
                datasets: [{
                    label: 'Jumlah Puskesmas',
                    data: itemsData.map(i => i.jumlah_puskesmas),
                    backgroundColor: barFill,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip },
                scales: {
                    y: { beginAtZero: true, grid: { color: tone.grid }, border: { display: false } },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, autoSkip: false, minRotation: 45, maxRotation: 70 }
                    }
                }
            }
        });

        // Doughnut chart: 8 wilayah terbesar + sisanya (semua dari data dataset)
        const sorted = [...itemsData].sort((a, b) => b.jumlah_puskesmas - a.jumlah_puskesmas);
        const top = sorted.slice(0, 8);
        const rest = sorted.slice(8).reduce((sum, i) => sum + i.jumlah_puskesmas, 0);
        const labels = top.map(i => shortName(i.nama_kabupaten_kota));
        const values = top.map(i => i.jumlah_puskesmas);
        if (rest > 0) { labels.push('Wilayah lainnya'); values.push(rest); }

        new Chart(document.getElementById('donutChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    // Selang-seling biru dan ungu dengan terang berbeda agar irisan mudah dibedakan
                    backgroundColor: ['#0073BB', '#8C43FC', '#4DA3DB', '#A166FF', '#0052CC', '#6F42C1', '#7CC4F0', '#C9B6FF', '#64748B'],
                    borderColor: tone.surface,
                    borderWidth: 2,
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    tooltip,
                    legend: {
                        position: 'bottom',
                        labels: { color: tone.legend, usePointStyle: true, boxWidth: 8, padding: 12, font: { size: 10 } }
                    }
                }
            }
        });
    }

    // Pencarian tabel
    (function () {
        const input = document.getElementById('search-input');
        const rows = document.querySelectorAll('#table-body tr[data-row]');
        const empty = document.getElementById('table-empty');
        const count = document.getElementById('table-count');
        if (!input) return;

        input.addEventListener('input', function (e) {
            const query = e.target.value.trim().toLowerCase();
            let visible = 0;
            rows.forEach(row => {
                const match = row.innerText.toLowerCase().includes(query);
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            if (rows.length > 0) {
                empty.classList.toggle('hidden', visible > 0);
                count.textContent = visible + ' dari ' + rows.length + ' wilayah ditampilkan';
            }
        });
    })();
</script>
@endpush