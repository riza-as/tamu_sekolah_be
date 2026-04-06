<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorType extends Model
{
    protected $table = 'visitor_types';
    protected $fillable = ['name'];
}
