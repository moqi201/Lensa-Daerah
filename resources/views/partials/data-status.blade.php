{{--
    Status sumber data (dipakai halaman direktori dan dashboard).
    Variabel: $meta  (array meta dari DetailPuskesmas / PuskesmasJabar::getProcessedData)
--}}
@php
    $source    = $meta['source'] ?? 'UNAVAILABLE';
    $tz        = config('app.timezone');
    $fetchedAt = !empty($meta['fetched_at'])
        ? \Illuminate\Support\Carbon::parse($meta['fetched_at'])->timezone($tz)->format('d/m/Y H:i') . ' ' . $tz
        : null;

    $tone = [
        'ok'   => 'border-emerald-400/30 bg-emerald-500/10 text-emerald-300',
        'warn' => 'border-amber-400/30 bg-amber-500/10 text-amber-300',
        'bad'  => 'border-rose-400/30 bg-rose-500/10 text-rose-300',
    ];

    $classLabels = [
        'VALID_LENGKAP'             => ['Valid dan lengkap', $tone['ok']],
        'VALID_DENGAN_FIELD_KOSONG' => ['Valid, sebagian field kosong', $tone['ok']],
        'BELUM_DIVERIFIKASI'        => ['Kelengkapan belum diverifikasi', $tone['warn']],
        'TIDAK_VALID'               => ['Data tidak valid', $tone['bad']],
        'DATA_TIDAK_TERSEDIA'       => ['Data tidak tersedia', $tone['bad']],
    ];
    $classification = $meta['classification'] ?? null;
    $classBadge     = $classLabels[$classification] ?? null;
    $warnings       = $meta['warnings'] ?? [];
@endphp

<div class="mb-6 space-y-3">
    @if($source === 'UNAVAILABLE')
        <div role="alert" class="rounded-2xl border border-rose-400/30 bg-rose-500/10 p-5">
            <h3 class="text-sm font-bold text-rose-200"><i class="fa-solid fa-triangle-exclamation mr-1.5" aria-hidden="true"></i> Data sementara tidak tersedia</h3>
            <p class="mt-1 text-xs leading-relaxed text-rose-100/80">
                {{ $meta['error'] ?? 'API sumber tidak dapat diakses.' }}
                Tidak ada salinan data valid yang dapat dipakai, jadi tidak ada data yang ditampilkan. Muat ulang halaman beberapa saat lagi.
            </p>
        </div>
    @elseif($source === 'CACHE_STALE')
        <div role="status" class="rounded-2xl border border-amber-400/30 bg-amber-500/10 p-5">
            <h3 class="text-sm font-bold text-amber-200"><i class="fa-solid fa-clock-rotate-left mr-1.5" aria-hidden="true"></i> Menampilkan salinan lama</h3>
            <p class="mt-1 text-xs leading-relaxed text-amber-100/80">
                {{ $meta['error'] ?? 'API sumber tidak dapat diakses.' }}
                Data di bawah adalah salinan terakhir yang berhasil diambil dari API pada {{ $fetchedAt }} dan mungkin sudah tidak terkini.
            </p>
        </div>
    @elseif($source === 'CACHE')
        <div role="status" class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
            <p class="text-xs text-slate-300">
                <i class="fa-solid fa-database mr-1.5 text-mist" aria-hidden="true"></i> Data berasal dari cache. Terakhir diambil dari API pada {{ $fetchedAt }}.
            </p>
        </div>
    @endif

    @if($source !== 'UNAVAILABLE' && $classBadge)
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-slate-400">Status data:</span>
            <span class="rounded-lg border px-2.5 py-1 font-semibold {{ $classBadge[1] }}">{{ $classBadge[0] }}</span>
            @if($source === 'LIVE_REST_API' && $fetchedAt)
                <span class="text-slate-500">Diambil dari API pada {{ $fetchedAt }}</span>
            @endif
        </div>
    @endif

    @if(count($warnings) > 0 && $source !== 'UNAVAILABLE')
        <details class="rounded-2xl border border-white/10 bg-white/[0.04] p-4 text-xs text-slate-300">
            <summary class="cursor-pointer font-semibold text-slate-100">Catatan kualitas data ({{ count($warnings) }})</summary>
            <ul class="mt-3 list-disc space-y-1.5 pl-5 leading-relaxed text-slate-300">
                @foreach($warnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </details>
    @endif
</div>