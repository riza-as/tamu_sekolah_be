<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    protected $table = 'qr_codes';
    protected $fillable = [
        'village_code',
        'link_qr_code',
        'status'
    ];

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    protected $casts = [
        'status' => 'integer'
    ];
}
