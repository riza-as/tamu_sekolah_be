<?php

namespace App\Http\Controllers\V1;

use App\Services\VillageService;
use Illuminate\Http\Request;

class VillageController
{
    public function __construct(private VillageService $villageService)
    {
        return $this->villageService = $villageService;
    }

    public function index()
    {
        return $this->villageService->getVillage();
    }

    public function show($id)
    {
        return $this->villageService->getVillageById($id);
    }
    public function store(Request $request, $subdistrict_code)
    {
        return $this->villageService->storeVillage($request, $subdistrict_code);
    }

    public function update(Request $request, $subdistrict_code, $id)
    {
        return $this->villageService->updateVillage($request, $subdistrict_code, $id);
    }

    public function delete($subdistrict_code, $id)
    {
        return $this->villageService->deleteVillage($subdistrict_code, $id);
    }
}
