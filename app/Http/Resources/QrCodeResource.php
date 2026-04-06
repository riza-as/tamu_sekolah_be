<?php

namespace App\Http\Resources;

use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QrCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'village_code' => new VillageResource(Village::where('code', $this->village_code)->first()),
            'link_qr_code' => $this->link_qr_code,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
    }
}
