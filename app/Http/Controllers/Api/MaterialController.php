<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'message' => 'Guest belum memiliki materi tersimpan. Materi guest hanya berlaku sampai halaman di-refresh.',
                'data' => [],
                'guest' => true,
            ]);
        }

        $search = $request->string('search')->toString();

        $materials = $this->materialQuery()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->limit((int) $request->integer('limit', 20))
            ->get();

        return response()->json([
            'message' => 'Daftar materi berhasil diambil.',
            'data' => $materials,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:80'],
            'content' => ['required', 'string', 'min:40'],
        ]);

        if (! Auth::check()) {
            return response()->json([
                'message' => 'Materi berhasil dipakai sementara. Login untuk menyimpan materi.',
                'data' => [
                    ...$validated,
                    'id' => null,
                    '_id' => null,
                    'guest_material' => true,
                ],
                'guest' => true,
            ], 201);
        }

        $material = Material::query()->create(array_merge($validated, [
            'user_id' => (string) Auth::id(),
            'created_by' => implode(' / ', $this->team()),
        ]));

        return response()->json([
            'message' => 'Materi berhasil disimpan ke MongoDB.',
            'data' => $material,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $material = $this->materialQuery()->findOrFail($id);
        
        $latestAi = \App\Models\AiGeneration::query()
            ->where('user_id', (string) Auth::id())
            ->where('material_id', $id)
            ->latest()
            ->first();

        return response()->json([
            'message' => 'Detail materi berhasil diambil.',
            'data' => array_merge($material->toArray(), [
                'latest_ai_result' => $latestAi ? $latestAi->result : null,
            ]),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $material = $this->materialQuery()->findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:120'],
            'subject' => ['sometimes', 'required', 'string', 'max:80'],
            'content' => ['sometimes', 'required', 'string', 'min:40'],
        ]);

        $material->update($validated);

        return response()->json([
            'message' => 'Materi berhasil diperbarui.',
            'data' => $material->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $material = $this->materialQuery()->findOrFail($id);
        $material->delete();

        return response()->json([
            'message' => 'Materi berhasil dihapus.',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function team(): array
    {
        return array_filter([
            (string) config('services.team.member_1'),
            (string) config('services.team.member_2'),
        ]);
    }

    private function materialQuery()
    {
        return Material::query()->where('user_id', (string) Auth::id());
    }
}
