<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubdistrictQrCode extends Model
{
    protected $table = 'subdistrict_qr_codes';

    protected $fillable = [
        'subdistrict_code',
        'link_qr_code',
        'status',
    ];

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class, 'subdistrict_code', 'code');
    }
}
