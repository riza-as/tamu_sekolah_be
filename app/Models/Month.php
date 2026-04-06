<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Month extends Model
{
    protected $table = 'months';
    public $fillable = [
        'month_number',
        'month_name'
    ];
}
