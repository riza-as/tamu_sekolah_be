<?php

namespace App\Services;

use App\Http\Resources\ObjectiveResource;
use App\Models\Objective;

class ObjectiveService extends ResponseService
{
    public function getObjective()
    {
        $page = max(1, (int) request()->get('page', 1));
        $limit = max(1, (int) request()->get('limit', 40));

        // Use eager loading for query data
        $query = Objective::orderBy('objectives.created_at', 'asc');

        // Filter by params
        if (request()->hasAny(['name'])) {
            $query->when(request('name'), function ($q, $name) {
                $q->where('objectives.name', 'ilike', '%' . $name . '%');
            });
        }

        // Paginate data
        $objectives = $query->paginate($limit, ['objectives.*'], 'page', $page);

        // Check if data is empty
        if ($objectives->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Tujuan tidak ditemukan');
        }

        // Response JSON
        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data tujuan', ObjectiveResource::collection($objectives));
    }

    public function showObjective($id)
    {
        $objective = Objective::find($id);
        if ($objective) {
            return $this->successJsonResponse(200, null, 'Berhasil menampilkan data tujuan', new ObjectiveResource($objective));
        }
        return $this->errorJsonResponse(404, null, 'Tujuan tidak ditemukan');
    }

    public function storeObjective($request)
    {
        $data = $request->validate([
            'name' => 'required|unique:objectives,name',
        ], [
            'name.required' => 'Nama tujuan harus diisi',
            'name.unique' => 'Nama tujuan sudah ada',
        ]);

        $objective = Objective::create([
            'name' => $data['name']
        ]);
        $objective->save();

        return $this->createdJsonResponse(201, null, 'Tujuan berhasil ditambahkan', new ObjectiveResource($objective));
    }

    public function updateObjective($request, $id)
    {
        $data = $request->validate([
            'name' => 'nullable',
        ]);

        $objective = Objective::find($id);
        if (!$objective) {
            return $this->errorJsonResponse(404, null, 'Tujuan tidak ditemukan');
        }
        $data['name'] = $data['name'] ?? $objective->name;
        $objective->update($data);
        return $this->updatedJsonResponse(200, null, 'Tujuan berhasil diubah', new ObjectiveResource($objective));
    }

    public function destroyObjective($id)
    {
        $objective = Objective::find($id);
        if ($objective) {
            $objective->delete();
            return $this->successJsonResponse(200, null, 'Tujuan berhasil dihapus');
        }
        return $this->errorJsonResponse(404, null, 'Tujuan tidak ditemukan');
    }
}
