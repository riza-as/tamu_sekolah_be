<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $table = 'visitors';
    
    protected $fillable = [
        'fullname',
        'school_code',
        'address',
        'visitor_type_id',
        'photo_visitor',
        'objective_id',
        'information',
    ];

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function visitorType()
    {
        return $this->belongsTo(VisitorType::class);
    }

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }

    public function school()
    {
         return $this->belongsTo(School::class, 'school_code', 'school_code');
    }

}
