<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school' => $this->school, 
            'school_level' => $this->school?->level?->name,
            'school_status' => $this->school?->status?->name,
            'visitor_type' => new VisitorTypeResource($this->visitorType),
            'objective' => new ObjectiveResource($this->objective),
            'fullname' => $this->fullname,
            'photo_visitor' => $this->photo_visitor,
            'address' => $this->address,
            'information' => $this->information,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null
        ];
    }
}