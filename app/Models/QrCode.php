<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    protected $table = 'qr_codes';
    protected $fillable = [
        'village_code',
        'school_code',
        'link_qr_code',
        'status'
    ];

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function school()
    {
        return $this->belongsTo(
            School::class,
            'school_code',
            'school_code'
        );
    }

    protected $casts = [
        'status' => 'integer'
    ];
}
