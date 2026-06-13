<?php
// d:\LongWork\BMUD-BTL\SystemSafetyandSecurity\app\Models\SecurityReport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityReport extends Model
{
    protected $table = 'security_reports';
    public $timestamps = false; // Bảng này dùng timestamp custom created_at

    protected $fillable = [
        'start_date',
        'end_date',
        'summary',
        'total_attacks',
        'critical_events',
        'created_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'total_attacks' => 'integer',
        'critical_events' => 'integer',
    ];
}
