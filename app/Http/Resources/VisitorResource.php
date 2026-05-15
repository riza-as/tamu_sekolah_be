<?php

namespace App\Http\Resources;

use App\Models\Objective;
// use App\Models\Village;
use App\Models\School;
use App\Models\VisitorType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school' => School::where('school_code', $this->school_code)->first(),
            'school_level' => $this->school?->level?->name,
            'school_status' => $this->school?->status?->name,
            'visitor_type' => new VisitorTypeResource(VisitorType::where('id', $this->visitor_type_id)->first()),
            'fullname' => $this->fullname,
            'photo_visitor' => $this->photo_visitor,
            'address' => $this->address,
            'objective' => new ObjectiveResource(Objective::where('id', $this->objective_id)->first()),
            'information' => $this->information,
            'created_at' => $this->created_at
        ];
    }
}
