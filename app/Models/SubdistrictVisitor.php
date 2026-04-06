<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubdistrictVisitor extends Model
{
    protected $table = 'subdistrict_visitors';
    protected $fillable = [
        'subdistrict_code',
        'fullname',
        'address',
        'visitor_type_id',
        'photo_visitor',
        'objective_id',
        'information',
    ];

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class);
    }

    public function visitorType()
    {
        return $this->belongsTo(VisitorType::class);
    }

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }
}
