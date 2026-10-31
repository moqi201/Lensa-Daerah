<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetailPuskesmas;

class PuskesmasController extends Controller
{
    public function index(Request $request)
    {
        $selectedKabupaten = $request->query('kabupaten', 'SEMUA');
        $searchQuery = $request->query('q', '');

        $data = DetailPuskesmas::getProcessedData($selectedKabupaten, $searchQuery);

        return view('puskesmas.index', [
            'data' => $data,
            'selectedKabupaten' => $selectedKabupaten,
            'searchQuery' => $searchQuery
        ]);
    }
}