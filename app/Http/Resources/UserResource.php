<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\School;
use App\Http\Resources\SchoolResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'level' => $this->level,
            'is_active' => $this->is_active,

            // 'profile' => $this->profile,
            'profile' => new ProfileResource($this->profile),

            'school' => $this->profile && $this->profile->school
                ? [
                    'name' => $this->profile->school->name,
                    'school_code' => $this->profile->school->school_code,
                ]
                : null,
        ];
    }
}
