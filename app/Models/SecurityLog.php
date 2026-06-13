<?php
// d:\LongWork\BMUD-BTL\SystemSafetyandSecurity\app\Models\SecurityLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    protected $table = 'security_logs';
    public $timestamps = false; // Bảng này dùng timestamp custom created_at tự động điền bởi DB

    protected $fillable = [
        'ip_address',
        'ma_nguoi_dung',
        'route',
        'request_method',
        'payload',
        'attack_type',
        'severity',
        'threat_score',
        'action_taken',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'threat_score' => 'integer',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung', 'ma_nguoi_dung');
    }
}
