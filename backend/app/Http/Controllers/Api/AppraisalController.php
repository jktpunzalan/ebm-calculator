<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appraisal\StoreAppraisalRequest;
use App\Http\Requests\Appraisal\UpdateAppraisalRequest;
use App\Http\Resources\AppraisalResource;
use App\Models\Appraisal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppraisalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $paginator = Appraisal::where('user_id', $userId)->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => AppraisalResource::collection($paginator->items()),
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    public function store(StoreAppraisalRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $appraisal = Appraisal::create($data);

        return response()->json([
            'success' => true,
            'data' => new AppraisalResource($appraisal),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $appraisal = Appraisal::where('user_id', $userId)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AppraisalResource($appraisal),
        ]);
    }

    public function update(UpdateAppraisalRequest $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $appraisal = Appraisal::where('user_id', $userId)->findOrFail($id);
        $appraisal->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => new AppraisalResource($appraisal),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $appraisal = Appraisal::where('user_id', $userId)->findOrFail($id);
        $appraisal->delete();
        return response()->json(['success' => true]);
    }
}
