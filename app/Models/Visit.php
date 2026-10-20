<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'applicant_name',
        'institution',
        'phone',
        'email',
        'participants_count',
        'purpose',
        'facility_target',
        'visit_date',
        'scheduled_date',
        'visit_time',
        'status',
        'whatsapp_status',
        'sheet_row_id',
        'last_synced_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'scheduled_date' => 'date',
        'last_synced_at' => 'datetime',
    ];

    const STATUS_MENUNGGU = 'MENUNGGU';
    const STATUS_DISETUJUI = 'DISETUJUI';
    const STATUS_DITOLAK = 'DITOLAK';

    const WA_BELUM = 'BELUM';
    const WA_TERKIRIM = 'TERKIRIM';

    public static function statuses(): array
    {
        return [self::STATUS_MENUNGGU, self::STATUS_DISETUJUI, self::STATUS_DITOLAK];
    }

    public static function whatsAppStatuses(): array
    {
        return [self::WA_BELUM, self::WA_TERKIRIM];
    }
}
