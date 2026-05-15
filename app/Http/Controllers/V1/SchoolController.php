<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\SchoolService;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function __construct(private SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    public function index()
    {
        return $this->schoolService->getSchool();
    }

    public function show($id)
    {
        return $this->schoolService->showSchool($id);
    }

    public function store(Request $request)
    {
        return $this->schoolService->storeSchool($request);
    }

    public function update(Request $request, $id)
    {
        return $this->schoolService->updateSchool($request, $id);
    }

    public function destroy($id)
    {
        return $this->schoolService->destroySchool($id);
    }
}