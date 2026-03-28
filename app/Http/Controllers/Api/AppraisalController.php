<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppraisalRequest;
use App\Http\Requests\UpdateAppraisalRequest;
use App\Http\Resources\AppraisalResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppraisalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $appraisals = $request->user()
            ->appraisals()
            ->with('results')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => AppraisalResource::collection($appraisals),
            'meta' => [
                'current_page' => $appraisals->currentPage(),
                'last_page' => $appraisals->lastPage(),
                'per_page' => $appraisals->perPage(),
                'total' => $appraisals->total(),
            ],
        ]);
    }

    public function store(StoreAppraisalRequest $request): JsonResponse
    {
        $appraisal = $request->user()->appraisals()->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => new AppraisalResource($appraisal),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $appraisal = $request->user()
            ->appraisals()
            ->with('results')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AppraisalResource($appraisal),
        ]);
    }

    public function update(UpdateAppraisalRequest $request, int $id): JsonResponse
    {
        $appraisal = $request->user()
            ->appraisals()
            ->findOrFail($id);

        $appraisal->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => new AppraisalResource($appraisal),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $appraisal = $request->user()
            ->appraisals()
            ->findOrFail($id);

        $appraisal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deleted',
        ]);
    }
}
