<?php

namespace App\Http\Resources;

use App\Http\Resources\DistrictResource;
use App\Http\Resources\ProvinceResource;
use App\Http\Resources\SubdistrictResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\VillageResource;
use App\Models\District;
use App\Models\Province;
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
            'user_id' => new UserResource(User::where('id', $this->user_id)->first()),
            'fullname' => $this->fullname,
            'photo_profile' => $this->photo_profile,
            'province_code' => new ProvinceResource(Province::where('code', $this->province_code)->first()),
            'district_code' => new DistrictResource(District::where('code', $this->district_code)->first()),
            'subdistrict_code' => new SubdistrictResource(Subdistrict::where('code', $this->subdistrict_code)->first()),
            'village_code' => new VillageResource(Village::where('code', $this->village_code)->first()),
        ];
    }
}
