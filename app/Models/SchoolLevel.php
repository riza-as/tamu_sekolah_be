<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolLevel extends Model
{
    protected   $table = 'school_levels';
    protected   $fillable = [
        'id',
        'name'
    ];
}
