<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicationRequest;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use App\Models\PubStatistic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPublicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $publications = Publication::with('stats')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => PublicationResource::collection($publications),
            'meta' => [
                'current_page' => $publications->currentPage(),
                'last_page' => $publications->lastPage(),
                'per_page' => $publications->perPage(),
                'total' => $publications->total(),
            ],
        ]);
    }

    public function store(StorePublicationRequest $request): JsonResponse
    {
        $slug = $request->slug;

        $htmlPath = $request->file('html_file')
            ->storeAs('publications', $slug . '.html', 'public');

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $ext = $request->file('thumbnail')->getClientOriginalExtension();
            $thumbnailPath = $request->file('thumbnail')
                ->storeAs('publications/thumbs', $slug . '.' . $ext, 'public');
        }

        $publication = Publication::create([
            'slug' => $slug,
            'title' => $request->title,
            'authors' => $request->authors,
            'type' => $request->type,
            'audience_tags' => $request->audience_tags,
            'html_file_path' => $htmlPath,
            'thumbnail_path' => $thumbnailPath,
            'is_active' => true,
            'published_at' => $request->published_at,
        ]);

        PubStatistic::create([
            'publication_id' => $publication->id,
            'views' => 0,
            'shares' => 0,
            'pdf_downloads' => 0,
            'saves' => 0,
        ]);

        $publication->load('stats');

        return response()->json([
            'success' => true,
            'data' => new PublicationResource($publication),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $publication = Publication::with('stats')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new PublicationResource($publication),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $publication = Publication::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:500',
            'authors' => 'nullable|string|max:500',
            'type' => 'nullable|in:therapy,diagnosis,harm,prognosis,systematic_review,economic_evaluation,cpg,screening,other',
            'audience_tags' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $publication->update(array_filter($validated, fn ($v) => $v !== null));

        if ($request->hasFile('html_file')) {
            $request->validate(['html_file' => 'file|mimes:html|max:5120']);
            $request->file('html_file')
                ->storeAs('publications', $publication->slug . '.html', 'public');
        }

        $publication->load('stats');

        return response()->json([
            'success' => true,
            'data' => new PublicationResource($publication),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $publication = Publication::findOrFail($id);

        if (Storage::disk('public')->exists($publication->html_file_path)) {
            Storage::disk('public')->delete($publication->html_file_path);
        }

        if ($publication->thumbnail_path && Storage::disk('public')->exists($publication->thumbnail_path)) {
            Storage::disk('public')->delete($publication->thumbnail_path);
        }

        $publication->delete();

        return response()->json([
            'success' => true,
            'message' => 'Publication deleted',
        ]);
    }
}
