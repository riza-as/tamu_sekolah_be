<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
{
   public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'address' => $this->address,
        'village_code' => $this->village_code,
        'school_code' => $this->school_code,
        'level_id' => $this->level_id,
        'status_id' => $this->status_id,

        // RELATION
        'village' => $this->village,
        'level' => $this->level,
        'status' => $this->status,
    ];
}
}
