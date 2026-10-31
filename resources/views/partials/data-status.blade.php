{{--
    Status sumber data (dipakai halaman direktori dan dashboard).
    Variabel: $meta  (array meta dari DetailPuskesmas / PuskesmasJabar::getProcessedData)
--}}
@php
    $source   = $meta['source'] ?? 'UNAVAILABLE';
    $tz       = config('app.timezone');
    $fetchedAt = !empty($meta['fetched_at'])
        ? \Illuminate\Support\Carbon::parse($meta['fetched_at'])->timezone($tz)->format('d/m/Y H:i') . ' ' . $tz
        : null;

    $classLabels = [
        'VALID_LENGKAP'             => ['Valid dan lengkap', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'VALID_DENGAN_FIELD_KOSONG' => ['Valid, sebagian field kosong', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'BELUM_DIVERIFIKASI'        => ['Kelengkapan belum diverifikasi', 'bg-amber-50 text-amber-700 border-amber-200'],
        'TIDAK_VALID'               => ['Data tidak valid', 'bg-rose-50 text-rose-700 border-rose-200'],
        'DATA_TIDAK_TERSEDIA'       => ['Data tidak tersedia', 'bg-rose-50 text-rose-700 border-rose-200'],
    ];
    $classification = $meta['classification'] ?? null;
    $classBadge     = $classLabels[$classification] ?? null;
    $warnings       = $meta['warnings'] ?? [];
@endphp

<div class="mb-6 space-y-3">
    @if($source === 'UNAVAILABLE')
        <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
            <h3 class="font-bold text-rose-800 text-sm"><i class="fa-solid fa-triangle-exclamation mr-1.5"></i> Data sementara tidak tersedia</h3>
            <p class="text-xs text-rose-700 mt-1 leading-relaxed">
                {{ $meta['error'] ?? 'API sumber tidak dapat diakses.' }}
                Tidak ada salinan data valid yang dapat dipakai, jadi tidak ada data yang ditampilkan. Muat ulang halaman beberapa saat lagi.
            </p>
        </div>
    @elseif($source === 'CACHE_STALE')
        <div role="status" class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <h3 class="font-bold text-amber-800 text-sm"><i class="fa-solid fa-clock-rotate-left mr-1.5"></i> Menampilkan salinan lama</h3>
            <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                {{ $meta['error'] ?? 'API sumber tidak dapat diakses.' }}
                Data di bawah adalah salinan terakhir yang berhasil diambil dari API pada {{ $fetchedAt }} dan mungkin sudah tidak terkini.
            </p>
        </div>
    @elseif($source === 'CACHE')
        <div role="status" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs text-slate-600">
                <i class="fa-solid fa-database mr-1.5"></i> Data berasal dari cache. Terakhir diambil dari API pada {{ $fetchedAt }}.
            </p>
        </div>
    @endif

    @if($source !== 'UNAVAILABLE' && $classBadge)
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-slate-500">Status data:</span>
            <span class="px-2.5 py-1 rounded-lg border font-semibold {{ $classBadge[1] }}">{{ $classBadge[0] }}</span>
            @if($source === 'LIVE_REST_API' && $fetchedAt)
                <span class="text-slate-400">Diambil dari API pada {{ $fetchedAt }}</span>
            @endif
        </div>
    @endif

    @if(count($warnings) > 0 && $source !== 'UNAVAILABLE')
        <details class="rounded-2xl border border-slate-200 bg-white p-4 text-xs text-slate-600">
            <summary class="cursor-pointer font-semibold text-slate-700">Catatan kualitas data ({{ count($warnings) }})</summary>
            <ul class="mt-3 space-y-1.5 list-disc pl-5 leading-relaxed">
                @foreach($warnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </details>
    @endif
</div>