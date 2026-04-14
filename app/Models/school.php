<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $table = 'schools';

    protected $fillable = [
        'id',
        'name',
        'address',
        'village_id',
        'level_id',
        'status_id'
    ];

    public $incrementing = false; // karena pakai NPSN
    protected $keyType = 'int';
}
