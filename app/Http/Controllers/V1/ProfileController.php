<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function index()
    {
        return $this->profileService->getProfileList();
    }

    public function me()
    {
        return $this->profileService->getMyProfile();
    }

    public function show($id)
    {
        return $this->profileService->getProfileDetail($id);
    }

    public function store(Request $request, $id)
    {
        return $this->profileService->storeProfile($request, $id);
    }

    public function update(Request $request, $id)
    {
        return $this->profileService->updateProfile($request, $id);
    }

    public function delete($id)
    {
        return $this->profileService->deleteProfile($id);
    }
}
