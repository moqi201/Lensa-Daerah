<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    */
    'http' => [
        'timeout'          => 15,
        'connect_timeout'  => 5,
        'ca_bundle'        => env('PUSKESMAS_CA_BUNDLE'),
        'insecure_ssl'     => filter_var(env('PUSKESMAS_INSECURE_SSL', true), FILTER_VALIDATE_BOOLEAN),
        'force_ip_resolve' => env('PUSKESMAS_FORCE_IP', 'v4'),
        'user_agent'       => env('PUSKESMAS_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache (detik)
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'ttl'       => 3600,
        'stale_ttl' => 604800,
        'cooldown'  => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dataset Agregat Jumlah Puskesmas (Galura API Resmi)
    |--------------------------------------------------------------------------
    */
    'aggregate' => [
        'endpoint' => 'https://galura.jabarprov.go.id/api/bigdata/jumlah-puskesmas-berdasarkan-kabupatenkota-di-jawa-barat',
        'limit'    => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dataset Detail Puskesmas (Galura API Resmi)
    |--------------------------------------------------------------------------
    */
    'detail' => [
        'endpoint'   => 'https://galura.jabarprov.go.id/api/bigdata/jumlah-puskesmas-berdasarkan-kabupatenkota-di-jawa-barat',
        'limit'      => 100,
        'paginate'   => false,
        'skip_param' => 'page',
        'max_pages'  => 5,
    ],

    'total_paths' => [
        'meta.total',
        'metadata.total',
        'pagination.total',
        'total',
    ],

    'fields' => [
        'required' => ['nama_kabupaten_kota', 'jumlah_puskesmas'],
        'optional' => ['kode_kabupaten_kota', 'satuan', 'tahun'],
    ],

    'reference_regions' => [
        'KABUPATEN BANDUNG BARAT', 'KABUPATEN BANDUNG', 'KOTA BANDUNG', 'KABUPATEN BOGOR', 'KOTA BOGOR',
        'KABUPATEN SUKABUMI', 'KOTA SUKABUMI', 'KABUPATEN CIANJUR', 'KABUPATEN GARUT', 'KABUPATEN TASIKMALAYA',
        'KOTA TASIKMALAYA', 'KABUPATEN CIAMIS', 'KABUPATEN KUNINGAN', 'KABUPATEN CIREBON', 'KOTA CIREBON',
        'KABUPATEN MAJALENGKA', 'KABUPATEN SUMEDANG', 'KABUPATEN INDRAMAYU', 'KABUPATEN SUBANG', 'KABUPATEN PURWAKARTA',
        'KABUPATEN KARAWANG', 'KABUPATEN BEKASI', 'KOTA BEKASI', 'KOTA DEPOK', 'KOTA CIMAHI',
        'KABUPATEN PANGANDARAN', 'KOTA BANJAR',
    ],
];