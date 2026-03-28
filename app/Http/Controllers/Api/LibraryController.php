<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLibraryRequest;
use App\Http\Resources\StudyLibraryResource;
use App\Models\Appraisal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()
            ->studyLibrary()
            ->with('appraisal')
            ->latest();

        if ($request->has('study_type')) {
            $query->where('study_type', $request->query('study_type'));
        }

        if ($request->has('validity_label')) {
            $query->where('validity_label', $request->query('validity_label'));
        }

        if ($request->has('starred')) {
            $query->where('is_starred', filter_var($request->query('starred'), FILTER_VALIDATE_BOOLEAN));
        }

        $library = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => StudyLibraryResource::collection($library),
            'meta' => [
                'current_page' => $library->currentPage(),
                'last_page' => $library->lastPage(),
                'per_page' => $library->perPage(),
                'total' => $library->total(),
            ],
        ]);
    }

    public function store(StoreLibraryRequest $request): JsonResponse
    {
        $appraisal = Appraisal::findOrFail($request->appraisal_id);

        if ($appraisal->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        $entry = $request->user()->studyLibrary()->create([
            'appraisal_id' => $appraisal->id,
            'title' => $appraisal->title,
            'study_type' => $appraisal->study_type,
            'key_result_label' => $request->key_result_label,
            'key_result_value' => $request->key_result_value,
            'validity_label' => $request->validity_label,
        ]);

        return response()->json([
            'success' => true,
            'data' => new StudyLibraryResource($entry),
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $entry = $request->user()
            ->studyLibrary()
            ->findOrFail($id);

        $entry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from library',
        ]);
    }
}
