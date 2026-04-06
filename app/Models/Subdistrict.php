<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subdistrict extends Model
{
    protected $table = 'subdistricts';
    protected $fillable =[
        'district_code',
        'code',
        'name'
    ];
}
