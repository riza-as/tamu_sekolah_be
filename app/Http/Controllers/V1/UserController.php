<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(private UserService $userService)
    {
        return $this->userService = $userService;
    }

    public function index()
    {
        return $this->userService->getUserList();
    }

    public function me()
    {
        return $this->userService->getMyUser();
    }

    public function show($id)
    {
        return $this->userService->getUserDetail($id);
    }

    public function store(UserRequest $request)
    {
        return $this->userService->storeUser($request);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        return $this->userService->updateUser($request, $id);
    }

    public function activate($id)
    {
        return $this->userService->activateUser($id);
    }

    public function destroy($id)
    {
        return $this->userService->destroyUser($id);
    }
}
