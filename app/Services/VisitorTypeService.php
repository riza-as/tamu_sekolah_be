<?php

namespace App\Services;

use App\Http\Resources\VisitorTypeResource;
use App\Models\VisitorType;

class VisitorTypeService extends ResponseService
{
    public function getVisitorType()
    {
        $page = max(1, (int) request()->get('page', 1));
        $limit = max(1, (int) request()->get('limit', 40));

        // Use eager loading for query data
        $query = VisitorType::orderBy('visitor_types.created_at', 'asc');

        // Filter by params
        if (request()->hasAny(['name'])) {
            $query->when(request('name'), function ($q, $name) {
                $q->where('visitor_types.name', 'ilike', '%' . $name . '%');
            });
        }

        // Paginate data
        $visitorTypes = $query->paginate($limit, ['visitor_types.*'], 'page', $page);

        // Check if data is empty
        if ($visitorTypes->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Tipe pengunjung tidak ditemukan');
        }

        // Response JSON
        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data tipe pengunjung', VisitorTypeResource::collection($visitorTypes));
    }

    public function getVisitorTypeById($id)
    {
        $visitorType = VisitorType::find($id);
        if (!$visitorType) {
            return $this->errorJsonResponse(404, null, 'Tipe pengunjung tidak ditemukan');
        }
        return $this->successJsonResponse(200, null, 'Berhasil menampilkan data tipe pengunjung', new VisitorTypeResource($visitorType));
    }

    public function storeVisitorType($request)
    {
        $data = $request->validate([
            'name' => 'required|unique:visitor_types,name',
        ], [
            'name.required' => 'Nama tipe pengunjung harus diisi',
            'name.unique' => 'Nama tipe pengunjung sudah ada',
        ]);

        $visitorType = new VisitorType();
        $visitorType->name = $data['name'];

        if ($visitorType->save()) {
            return $this->createdJsonResponse(201, null, 'Tipe pengunjung berhasil ditambahkan', new VisitorTypeResource($visitorType));
        }
        return $this->errorJsonResponse(500, null, 'Tipe pengunjung gagal ditambahkan');
    }

    public function updateVisitorType($request, $id)
    {
        $data = $request->validate([
            'name' => 'nullable',
        ]);

        $visitorType = VisitorType::find($id);
        if (!$visitorType) {
            return $this->errorJsonResponse(404, null, 'Tipe pengunjung tidak ditemukan');
        }
        $data['name'] = $data['name'] ?? $visitorType->name;
        $visitorType->update($data);
        return $this->updatedJsonResponse(200, null, 'Tipe pengunjung berhasil diubah', new VisitorTypeResource($visitorType));
    }

    public function destroyVisitorType($id)
    {
        $visitorType = VisitorType::find($id);
        if (!$visitorType) {
            return $this->errorJsonResponse(404, null, 'Tipe pengunjung tidak ditemukan');
        }
        if ($visitorType->delete()) {
            return $this->successJsonResponse(200, null, 'Tipe pengunjung berhasil dihapus');
        }
        return $this->errorJsonResponse(500, null, 'Tipe pengunjung gagal dihapus');
    }
}
