<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $fillable = [
        'endpoint',
        'status_code',
        'response_time_ms',
        'payload_summary',
    ];
}