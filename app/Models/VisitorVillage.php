<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorVillage extends Model
{
    protected $table = 'visitor_villages';

    protected $fillable = [
        'village_code',
        'visitor_id',
    ];

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }
}
