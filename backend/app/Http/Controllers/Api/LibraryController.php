<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\StoreLibraryRequest;
use App\Http\Resources\StudyLibraryResource;
use App\Models\StudyLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $paginator = StudyLibrary::where('user_id', $userId)->latest('saved_at')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => StudyLibraryResource::collection($paginator->items()),
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    public function store(StoreLibraryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $item = StudyLibrary::create($data);

        return response()->json([
            'success' => true,
            'data' => new StudyLibraryResource($item),
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $item = StudyLibrary::where('user_id', $userId)->findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }
}
