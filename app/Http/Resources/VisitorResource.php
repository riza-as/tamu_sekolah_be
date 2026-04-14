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
            'school' => School::where('id', $this->school_id)->first(),
            // 'village' => new VillageResource(Village::where('code', $this->village_code)->first()),
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
