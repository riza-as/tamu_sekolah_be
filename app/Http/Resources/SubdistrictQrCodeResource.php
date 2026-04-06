<?php

namespace App\Http\Resources;

use App\Models\Subdistrict;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubdistrictQrCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subdistrict_code' => new SubdistrictResource(Subdistrict::where('code', $this->subdistrict_code)->first()),
            'link_qr_code' => $this->link_qr_code,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
    }
}
