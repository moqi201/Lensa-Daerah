<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuskesmasCoordinate extends Model
{
    protected $fillable = [
        'kode_puskesmas', 'latitude', 'longitude', 'status_verifikasi', 'metode_input'
    ];
}