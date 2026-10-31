@extends('layouts.app')

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

    $num = fn ($n) => $n === null ? '—' : number_format($n, 0, ',', '.');
    $hasItems = count($data['items']) > 0;
@endphp

<!-- HERO & FILTER SECTION -->
<section class="relative pt-8 pb-10 bg-gradient-to-b from-white via-slate-50 to-slate-100/50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:flex lg:items-center lg:justify-between gap-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold mb-3">
                    <i class="fa-solid fa-shield-halved text-brand-600"></i> Kompetisi Web In Action (WIA)
                </div>
                <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 leading-tight">
                    Transparansi Fasilitas Kesehatan & Data Publik Jawa Barat
                </h1>
                <p class="mt-3 text-base sm:text-lg text-slate-600 leading-relaxed">
                    Platform pengolah REST API Open Data Jawa Barat. Menyajikan jumlah Puskesmas per Kabupaten & Kota apa adanya dari sumber, lengkap dengan status dan keterbatasan datanya.
                </p>
            </div>

            <!-- FILTER FORM -->
            <div class="mt-6 lg:mt-0 bg-white p-5 rounded-2xl shadow-lg shadow-slate-200/50 border border-slate-200 min-w-[320px]">
                <form method="GET" action="{{ route('dashboard') }}">
                    <label for="kabupaten" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-map-location-dot text-brand-600 mr-1.5"></i> Pilih Wilayah Kab / Kota:
                    </label>
                    <select id="kabupaten" name="kabupaten" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 p-3 font-semibold transition-all">
                        <option value="SEMUA" {{ $selectedKabupaten == 'SEMUA' ? 'selected' : '' }}>-- Semua Kab/Kota Jawa Barat --</option>
                        @foreach($kabOptions as $kab)
                            <option value="{{ $kab }}" {{ $selectedKabupaten == $kab ? 'selected' : '' }}>{{ $kab }}</option>
                        @endforeach
                    </select>
                </form>
                <div class="mt-3 flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                    <span><i class="fa-solid fa-database mr-1"></i> Sumber: {{ $sourceLabel }}</span>
                    @if($meta['source'] === 'LIVE_REST_API' && $meta['latency'] !== null)
                        <span class="text-emerald-600 font-bold"><i class="fa-solid fa-bolt mr-1"></i> {{ $meta['latency'] }} ms</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- STATUS SUMBER DATA & CATATAN KUALITAS -->
        <div class="mt-8">
            @include('partials.data-status', ['meta' => $meta])
        </div>

        <!-- STAT CARDS (semua angka dapat ditelusuri ke dataset) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Puskesmas</span>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-hospital-user text-lg"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="text-3xl font-extrabold text-slate-900">{{ $num($summary['total_puskesmas']) }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Jumlah dari dataset, wilayah yang dipilih</p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Jumlah Wilayah</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fa-solid fa-map-location-dot text-lg"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="text-3xl font-extrabold text-slate-900">{{ $num($summary['total_wilayah']) }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Kabupaten/Kota pada data</p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Terbanyak</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="fa-solid fa-chart-column text-lg"></i>
                    </div>
                </div>
                <div class="mt-3">
                    @if($summary['wilayah_terbanyak'])
                        <h3 class="text-3xl font-extrabold text-slate-900">{{ $num($summary['wilayah_terbanyak']['jumlah_puskesmas']) }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $summary['wilayah_terbanyak']['nama_kabupaten_kota'] }}</p>
                    @else
                        <h3 class="text-3xl font-extrabold text-slate-900">—</h3>
                        <p class="text-xs text-slate-500 mt-1">Tidak ada data</p>
                    @endif
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Periode Data</span>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <i class="fa-solid fa-calendar-days text-lg"></i>
                    </div>
                </div>
                <div class="mt-3">
                    @if($summary['tahun'])
                        <h3 class="text-3xl font-extrabold text-slate-900">{{ $summary['tahun'] }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Tahun dataset yang ditampilkan</p>
                    @else
                        <h3 class="text-xl font-extrabold text-slate-500">Tidak dilaporkan</h3>
                        <p class="text-xs text-slate-500 mt-1">Sumber tidak menyertakan tahun</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CHARTS SECTION -->
<section class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @if($hasItems)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-800 text-base mb-4">Sebaran Puskesmas per Wilayah</h3>
                <div class="h-80">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-800 text-base mb-4">Proporsi Puskesmas per Wilayah</h3>
                <div class="h-80 flex items-center justify-center">
                    <canvas id="donutChart"></canvas>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white p-10 rounded-2xl border border-slate-200 text-center">
            <i class="fa-solid fa-chart-column text-4xl text-slate-300 mb-3"></i>
            <h3 class="font-bold text-slate-700">Grafik belum dapat ditampilkan</h3>
            <p class="text-slate-500 text-xs mt-1">Tidak ada data untuk wilayah ini atau sumber data sedang tidak tersedia. Lihat status data di bagian atas halaman.</p>
        </div>
    @endif
</section>

<!-- DATA TABLE SECTION -->
<section class="py-10 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div>
                <h2 class="font-serif text-2xl font-bold text-slate-900">Rincian Data Wilayah Jawa Barat</h2>
                <p class="text-slate-600 text-sm mt-1">Menampilkan {{ count($data['items']) }} wilayah dari dataset jumlah Puskesmas</p>
            </div>
            <input type="text" id="search-input" placeholder="Cari nama kabupaten/kota..." class="mt-3 md:mt-0 px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium w-full md:w-64">
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-700 uppercase bg-slate-100/90 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Kabupaten / Kota</th>
                        <th class="px-6 py-4">Jumlah Puskesmas</th>
                        <th class="px-6 py-4">Tahun</th>
                        <th class="px-6 py-4">Porsi dari total</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y divide-slate-200 bg-white">
                    @forelse($data['items'] as $index => $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $item['nama_kabupaten_kota'] }}</td>
                            <td class="px-6 py-4 font-semibold text-blue-600">{{ $item['jumlah_puskesmas'] }} Unit</td>
                            <td class="px-6 py-4 text-slate-600">{{ $item['tahun'] ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-600">
                                @if(($summary['total_puskesmas'] ?? 0) > 0)
                                    {{ number_format($item['jumlah_puskesmas'] / $summary['total_puskesmas'] * 100, 1, ',', '.') }}%
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                {{ $meta['source'] === 'UNAVAILABLE' ? 'Data sementara tidak tersedia.' : 'Data tidak ditemukan.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- API INSPECTOR -->
<section class="py-10 bg-slate-900 text-white border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="text-xs font-bold text-brand-400 uppercase tracking-widest"><i class="fa-solid fa-terminal mr-1"></i> Live Verification</span>
                <h2 class="font-serif text-xl font-bold">Laravel API Proxy Inspector</h2>
            </div>
            <span class="px-3 py-1 bg-slate-800 text-slate-300 rounded-lg text-xs font-mono border border-slate-700">
                {{ $meta['source'] }}@if(!empty($meta['error_code'])) / {{ $meta['error_code'] }}@endif
            </span>
        </div>

        <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 font-mono text-xs overflow-x-auto leading-relaxed">
            <div class="text-slate-400 mb-2 border-b border-slate-800 pb-2 flex justify-between">
                <span>Endpoint: <span class="text-emerald-400">{{ $meta['endpoint'] }}</span></span>
                @if($meta['source'] === 'LIVE_REST_API' && $meta['latency'] !== null)
                    <span class="text-amber-400">Latency: {{ $meta['latency'] }} ms</span>
                @endif
            </div>
            <p class="text-slate-500 mb-2">Contoh 3 baris pertama setelah diproses oleh backend (bukan respons mentah API).</p>
            <pre class="text-slate-300 max-h-40 overflow-y-auto">{{ json_encode(array_slice($data['items'], 0, 3), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const itemsData = @json($data['items']);
    const shortName = (name) => name.replace(/^KABUPATEN /i, 'KAB. ');

    if (itemsData.length > 0 && document.getElementById('barChart')) {
        // Bar chart: jumlah Puskesmas per wilayah
        new Chart(document.getElementById('barChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: itemsData.map(i => shortName(i.nama_kabupaten_kota)),
                datasets: [{
                    label: 'Jumlah Puskesmas',
                    data: itemsData.map(i => i.jumlah_puskesmas),
                    backgroundColor: '#16a34a',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true },
                    x: { ticks: { font: { size: 10 } } }
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
                    backgroundColor: ['#2563eb', '#f59e0b', '#10b981', '#f43f5e', '#8b5cf6', '#0ea5e9', '#84cc16', '#ec4899', '#94a3b8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } }
            }
        });
    }

    // Search Filter Input
    document.getElementById('search-input').addEventListener('input', function (e) {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('#table-body tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
        });
    });
</script>
@endpush