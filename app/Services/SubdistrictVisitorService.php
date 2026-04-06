<?php

namespace App\Services;

use App\Http\Resources\SubdistrictVisitorResource;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\SubdistrictVisitor;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class SubdistrictVisitorService extends ResponseService
{
    public function getSubdistrictVisitorTotal()
    {
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        $thisWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;
        if ($user->level === 2) {
            $visitorTotal = SubdistrictVisitor::where('subdistrict_code', $user->profile->subdistrict_code)->count();
            $visitorToday = SubdistrictVisitor::where('subdistrict_code', $user->profile->subdistrict_code)->whereDate('created_at', $today)->count();
            $visitorWeek  = SubdistrictVisitor::where('subdistrict_code', $user->profile->subdistrict_code)->whereBetween('created_at', [$thisWeek, Carbon::now()->endOfWeek()])->count();
            $visitorMonth = SubdistrictVisitor::where('subdistrict_code', $user->profile->subdistrict_code)->whereMonth('created_at', $thisMonth)->count();
            $visitorYear  = SubdistrictVisitor::where('subdistrict_code', $user->profile->subdistrict_code)->whereYear('created_at', $thisYear)->count();
        } else {
            $visitorTotal = SubdistrictVisitor::count();
            $visitorToday = SubdistrictVisitor::whereDate('created_at', $today)->count();
            $visitorWeek  = SubdistrictVisitor::whereBetween('created_at', [$thisWeek, Carbon::now()->endOfWeek()])->count();
            $visitorMonth = SubdistrictVisitor::whereMonth('created_at', $thisMonth)->count();
            $visitorYear  = SubdistrictVisitor::whereYear('created_at', $thisYear)->count();
        }
        return response()->json([
            "code" => 200,
            "status" => "success",
            "message" => "Berhasil menampilkan jumlah pengunjung kecamatan",
            "data" => [
                "total_visitor" => $visitorTotal,
                "visitor_today" => $visitorToday,
                "visitor_week"  => $visitorWeek,
                "visitor_month" => $visitorMonth,
                "visitor_year"  => $visitorYear
            ]
        ]);
    }

    public function chartSubdistrictVisitor()
    {
        $user = Auth::user();
        $year = request()->get('year') ?? now()->year;

        $total = DB::select("SELECT
                m.month_number AS month_number,
                m.month_name AS month_name,
                -- COALESCE(profiles.subdistrict_code, 0) AS subdistrict_code,
                CASE
                    WHEN COALESCE(profiles.subdistrict_code, 0) = 0
                    THEN 0
                    ELSE COALESCE(COUNT(DISTINCT v.id), 0)
                END AS total_visitor
                FROM
                    months m
                LEFT JOIN
                    subdistrict_visitors v
                    ON CAST(m.month_number AS INTEGER) = EXTRACT(MONTH FROM v.created_at)
                    AND EXTRACT(YEAR FROM v.created_at) = ?
                LEFT JOIN
                    profiles
                    ON v.subdistrict_code = profiles.subdistrict_code
                    AND profiles.subdistrict_code = ?
                GROUP BY
                    m.month_number, m.month_name, profiles.subdistrict_code
                ORDER BY
                    CAST(m.month_number AS INTEGER);
            ", [$year, $user->profile->subdistrict_code]);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => "Diagram pengunjung berhasil ditampilkan",
            'data' => !empty($total) ? $total : null,
        ], 200);
    }

    public function getSubdistrictVisitors()
    {
        $user = Auth::user();
        $page = max(1, (int) request()->get('page', 1));
        $limit = max(1, (int) request()->get('limit', 40));
        // Use eager loading for query data
        $query = SubdistrictVisitor::with('subdistrict', 'visitorType', 'objective')
            ->leftJoin('subdistricts', 'subdistricts.code', '=', 'subdistrict_visitors.subdistrict_code')
            ->leftJoin('visitor_types', 'visitor_types.id', '=', 'subdistrict_visitors.visitor_type_id')
            ->leftJoin('objectives', 'objectives.id', '=', 'subdistrict_visitors.objective_id')
            // ->where('subdistricts.code', $user->profile->subdistrict_code)
            ->orderBy('subdistrict_visitors.created_at', 'desc');

        // Filter by params
        if (request()->hasAny(['name', 'subdistrict_code', 'visitor_type_id', 'objective_id', 'created_at'])) {
            $query->when(request('name'), function ($q, $name) {
                $q->where('subdistrict_visitors.fullname', 'ilike', '%' . $name . '%');
            })->when(request('subdistrict_code'), function ($q, $subdistrictCode) {
                $q->where('subdistrict_visitors.subdistrict_code', $subdistrictCode);
            })->when(request('visitor_type_id'), function ($q, $visitorTypeId) {
                $q->where('visitor_types.id', $visitorTypeId);
            })->when(request('objective_id'), function ($q, $objectiveId) {
                $q->where('objectives.id', $objectiveId);
            })->when(request('created_at'), function ($q, $createdAt) {
                // Gunakan Carbon untuk filtering berdasarkan waktu
                if ($createdAt === 'today') {
                    $q->whereDate('subdistrict_visitors.created_at', Carbon::today());
                } elseif ($createdAt === 'this_week') {
                    $q->whereBetween('subdistrict_visitors.created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                } elseif ($createdAt === 'this_month') {
                    $q->whereMonth('subdistrict_visitors.created_at', Carbon::now()->month);
                } elseif ($createdAt === 'this_year') {
                    $q->whereYear('subdistrict_visitors.created_at', Carbon::now()->year);
                } else {
                    // Jika created_at bukan nilai yang dikenali, fallback ke like biasa
                    $q->where('subdistrict_visitors.created_at', 'like', '%' . $createdAt . '%');
                }
            });
        } else if ($user->level === 3) {
            $query->where('subdistrict_visitors.subdistrict_code', $user->profile->subdistrict_code);
        }
        // Paginate data
        $subdistrict_visitors = $query->paginate($limit, ['subdistrict_visitors.*'], 'page', $page);

        // Check if data is empty
        if ($subdistrict_visitors->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Pengunjung kecamatan tidak ditemukan');
        }

        // Response JSON
        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data pengunjung kecamatan', SubdistrictVisitorResource::collection($subdistrict_visitors));
    }

    public function showSubdistrictVisitor($id)
    {
        $subdistrict_visitor = SubdistrictVisitor::find($id);
        if (!$subdistrict_visitor) {
            return $this->errorJsonResponse(404, null, 'Pengunjung kecamatan tidak ditemukan');
        }
        return $this->successJsonResponse(200, null, 'Berhasil menampilkan data pengunjung kecamatan', new SubdistrictVisitorResource($subdistrict_visitor));
    }

    public function storeSubdistrictVisitor($request, $subdistrict_code)
    {

        // Cek jika ada file yang diunggah
        if ($request->hasFile('photo_visitor')) {

            // Proses upload gambar baru
            $photoVisitor = uniqid() . '.' . $request->photo_visitor->extension();
            $img = Image::make($request->photo_visitor->path());

            // Resize jika lebar gambar lebih dari 720px
            if ($img->width() > 720) {
                $img->resize(null, 720, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            // Sesuaikan orientasi gambar jika perlu
            $img->orientate();

            // Simpan gambar ke Storage disk 'public'
            $path = 'uploads/photo_subdistrict_visitors/' . $photoVisitor;
            Storage::disk('public')->put($path, (string) $img->encode());

            // Update path gambar pada profil
            $photoVisitorUrl = Storage::url($path);
            $request->photo_visitor = $photoVisitorUrl;
            $data_request['photo_visitor'] = $photoVisitorUrl;
        }
        $subdistrict_visitor = SubdistrictVisitor::create([
            'visitor_type_id' => $request->visitor_type_id,
            'subdistrict_code' => $subdistrict_code,
            'fullname' => $request->fullname,
            'address' => $request->address,
            'photo_visitor' => url($request->photo_visitor),
            'objective_id' => $request->objective_id,
            'information' => $request->information
        ]);
        return $this->createdJsonResponse(201, null, 'Berhasil menambahkan data pengunjung kecamatan', new SubdistrictVisitorResource($subdistrict_visitor));
    }

    public function destroySubdistrictVisitor($id)
    {
        $subdistrict_visitor = SubdistrictVisitor::find($id);
        if (!$subdistrict_visitor) {
            return $this->errorJsonResponse(404, null, 'Pengunjung kecamatan tidak ditemukan');
        }

        // Hapus gambar lama jika ada
        if ($subdistrict_visitor->photo_visitor) {
            $filename = basename($subdistrict_visitor->photo_visitor);
            Storage::disk('public')->delete('uploads/photo_subdistrict_visitors/' . $filename);
        }
        $subdistrict_visitor->delete();
        return $this->successJsonResponse(200, null, 'Berhasil menghapus data pengunjung kecamatan');
    }
}
