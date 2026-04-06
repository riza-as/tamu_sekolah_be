<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\ObjectiveService;
use Illuminate\Http\Request;

class ObjectiveController extends Controller
{
    public function __construct(private ObjectiveService $objectiveService)
    {
        $this->objectiveService = $objectiveService;
    }

    public function index()
    {
        return $this->objectiveService->getObjective();
    }

    public function show($id)
    {
        return $this->objectiveService->showObjective($id);
    }

    public function store(Request $request)
    {
        return $this->objectiveService->storeObjective($request);
    }

    public function update(Request $request, $id)
    {
        return $this->objectiveService->updateObjective($request, $id);
    }

    public function destroy($id)
    {
        return $this->objectiveService->destroyObjective($id);
    }
}
