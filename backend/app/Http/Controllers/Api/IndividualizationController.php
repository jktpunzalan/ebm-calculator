<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Individualization\StoreIndividualizationRequest;
use App\Http\Resources\IndividualizationResource;
use App\Models\Individualization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndividualizationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $paginator = Individualization::where('user_id', $userId)->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => IndividualizationResource::collection($paginator->items()),
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    public function store(StoreIndividualizationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $item = Individualization::create($data);

        return response()->json([
            'success' => true,
            'data' => new IndividualizationResource($item),
        ], 201);
    }
}
