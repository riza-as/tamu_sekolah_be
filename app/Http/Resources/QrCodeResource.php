<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QrCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'school_code'  => $this->school_code,
            'school'       => $this->school ? new SchoolResource($this->school) : null,
            'link_qr_code' => $this->link_qr_code,
            'status'       => $this->status,
            'created_at'   => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at'   => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}