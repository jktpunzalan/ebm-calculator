<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIndividualizationRequest;
use App\Http\Resources\IndividualizationResource;
use App\Models\StudyLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndividualizationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()
            ->individualizations()
            ->with('library')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => IndividualizationResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(StoreIndividualizationRequest $request): JsonResponse
    {
        $library = StudyLibrary::findOrFail($request->library_id);

        if ($library->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        $individualization = $request->user()->individualizations()->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => new IndividualizationResource($individualization),
        ], 201);
    }
}
