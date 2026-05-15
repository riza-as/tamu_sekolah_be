<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolStatus extends Model
{
    protected   $table = 'school_statuses';
    protected   $fillable = [
        'id',
        'name'
    ];
}
