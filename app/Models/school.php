<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $table = 'schools';

    protected $primaryKey = 'school_code';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'address',
        'school_code',
        'village_code',
        'level_id',
        'status_id'
    ];

    public function level()
    {
        return $this->belongsTo(SchoolLevel::class, 'level_id');
    }

    public function status()
    {
        return $this->belongsTo(SchoolStatus::class, 'status_id');
    }
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_code', 'code');
    }
}
