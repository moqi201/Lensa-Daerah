<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SnapshotPuskesmas extends Command
{
    protected $signature = 'puskesmas:snapshot';
    protected $description = 'Tarik data Puskesmas dari API Jabar lalu simpan sebagai snapshot JSON';

    public function handle(): int
    {
        $url  = config('puskesmas.aggregate.endpoint');
        $rows = [];

        for ($page = 1; $page <= 20; $page++) {
            $res = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    'Accept'     => 'application/json',
                ])
                ->timeout(30)
                ->get($url, ['page' => $page, 'per_page' => 100, 'sort' => 'id:desc']);

            if (! $res->successful()) {
                $this->error("Gagal: HTTP {$res->status()} (halaman {$page})");
                return self::FAILURE;
            }

            $data = $res->json('data') ?? [];
            foreach ($data as $r) {
                $rows[] = $r;
            }
            if (count($data) < 100) {
                break;
            }
        }

        $records = [];
        foreach ($rows as $r) {
            if (! is_array($r) || empty($r['nama_kabupaten_kota']) || ! is_numeric($r['jumlah_puskesmas'] ?? null)) {
                continue;
            }
            $key = $r['id'] ?? uniqid();
            $records[$key] = [
                'id'                  => $r['id'] ?? null,
                'nama_kabupaten_kota' => mb_strtoupper(trim($r['nama_kabupaten_kota'])),
                'jumlah_puskesmas'    => (int) $r['jumlah_puskesmas'],
                'tahun'               => is_numeric($r['tahun'] ?? null) ? (int) $r['tahun'] : null,
            ];
        }
        $records = array_values($records);

        if (empty($records)) {
            $this->error('Tidak ada record valid.');
            return self::FAILURE;
        }

        if (! is_dir(database_path('data'))) {
            mkdir(database_path('data'), 0755, true);
        }
        file_put_contents(
            database_path('data/puskesmas.json'),
            json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->info(count($records) . ' record tersimpan di database/data/puskesmas.json');
        return self::SUCCESS;
    }
}