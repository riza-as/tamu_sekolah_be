<?php

namespace App\Http\Resources;

use App\Http\Resources\DistrictResource;
use App\Http\Resources\ProvinceResource;
use App\Http\Resources\SubdistrictResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\VillageResource;
use App\Models\District;
use App\Models\Province;
use App\Models\School;
use App\Models\Subdistrict;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,


            'user_id' => new UserResource($this->whenLoaded('user')) ?? $this->user_id,

            'fullname' => $this->fullname,

            'photo_profile' => $this->photo_profile,

            // Province
            'province_code' => $this->province_code != 0
                ? new ProvinceResource(Province::where('code', $this->province_code)->first())
                : new ProvinceResource($this->school?->village?->subdistrict?->district?->province),

            // District
            'district_code' => $this->district_code != 0
                ? new DistrictResource(
                    District::where('code', $this->district_code)->first()
                )
                : new DistrictResource(
                    $this->school?->village?->subdistrict?->district
                ),

            // Subdistrict
            'subdistrict_code' => $this->subdistrict_code != 0
                ? new SubdistrictResource(
                    Subdistrict::where('code', $this->subdistrict_code)->first()
                )
                : new SubdistrictResource(
                    $this->school?->village?->subdistrict
                ),

            // Village
            'village_code' => $this->village_code != 0
                ? new VillageResource(
                    Village::where('code', $this->village_code)->first()
                )
                : new VillageResource(
                    $this->school?->village
                ),

            // School
            'school_code' => $this->school_code,

            'school' => new SchoolResource($this->school),
        ];
    }
}
