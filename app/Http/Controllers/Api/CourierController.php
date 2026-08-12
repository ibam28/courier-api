<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CourierController extends Controller
{
    /**
     * GET /api/couriers
     *
     * Query string yang didukung:
     *  - search   : cari kurir berdasarkan nama (substring/space-separated match)
     *  - level    : daftar level yang ingin ditampilkan, dipisah koma (contoh "2,3")
     *  - sort     : "created_at" untuk override default sort (default: name)
     *  - order    : "asc" | "desc" (default: asc)
     *  - per_page : jumlah item per halaman (default: 15, max: 100)
     *  - page     : nomor halaman
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'level' => ['nullable', 'string', 'max:32'],
            'sort' => ['nullable', Rule::in(['name', 'created_at'])],
            'order' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Courier::query();

        // Search: cocokkan setiap token terhadap nama (semua token harus match)
        if (! empty($validated['search'])) {
            $tokens = preg_split('/\s+/', trim($validated['search']));
            foreach ($tokens as $token) {
                if ($token === '') {
                    continue;
                }
                $query->where('name', 'like', '%'.$token.'%');
            }
        }

        // Filter level: ?level=2,3 -> kurir dengan level 2 atau 3
        if (! empty($validated['level'])) {
            $levels = array_filter(array_map('trim', explode(',', $validated['level'])), fn ($v) => $v !== '');
            $levels = array_map('intval', $levels);
            $levels = array_values(array_filter($levels, fn ($v) => $v >= 1 && $v <= 5));
            if (! empty($levels)) {
                $query->whereIn('level', $levels);
            }
        }

        // Sorting: default by name, override dengan ?sort=created_at
        $sort = $validated['sort'] ?? 'name';
        $order = $validated['order'] ?? 'asc';
        $query->orderBy($sort, $order);

        // Pagination
        $perPage = $validated['per_page'] ?? 15;
        $couriers = $query->paginate($perPage)->withQueryString();

        return response()->json($couriers);
    }

    /**
     * GET /api/couriers/{courier}
     */
    public function show(Courier $courier): JsonResponse
    {
        return response()->json([
            'data' => $courier,
        ]);
    }

    /**
     * POST /api/couriers
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $this->validatePayload($request);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $courier = Courier::create($data);

        return response()->json([
            'message' => 'Courier created',
            'data' => $courier,
        ], 201);
    }

    /**
     * PUT/PATCH /api/couriers/{courier}
     */
    public function update(Request $request, Courier $courier): JsonResponse
    {
        try {
            $data = $this->validatePayload($request, $courier->id);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $courier->update($data);

        return response()->json([
            'message' => 'Courier updated',
            'data' => $courier->fresh(),
        ]);
    }

    /**
     * DELETE /api/couriers/{courier}
     */
    public function destroy(Courier $courier): JsonResponse
    {
        $id = $courier->id;
        $courier->delete();

        // Verifikasi: data memang sudah hilang dari database
        $stillExists = Courier::where('id', $id)->exists();

        return response()->json([
            'message' => 'Courier deleted',
            'id' => $id,
            'still_in_db' => $stillExists ? 'yes' : 'no',
        ]);
    }

    /**
     * Validasi input untuk store & update.
     *
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = Rule::unique('couriers', 'code');
        if ($ignoreId !== null) {
            $uniqueRule = $uniqueRule->ignore($ignoreId);
        }

        return $request->validate([
            'code' => ['nullable', 'string', 'max:32', $uniqueRule],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:1000'],
            'vehicle_type' => ['nullable', 'string', 'max:32'],
            'vehicle_plate' => ['nullable', 'string', 'max:32'],
            'level' => ['required', 'integer', 'between:1,5'],
            'status' => ['nullable', 'string', Rule::in(Courier::STATUSES)],
            'joined_at' => ['nullable', 'date'],
        ]);
    }
}
