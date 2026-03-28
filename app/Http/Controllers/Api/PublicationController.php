<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PubStatisticResource;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $publications = Publication::active()
            ->with('stats')
            ->orderByDesc('published_at')
            ->paginate(12);

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

    public function show(Request $request, string $slug): JsonResponse
    {
        $publication = Publication::active()
            ->with('stats')
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new PublicationResource($publication),
        ]);
    }

    public function recordView(Request $request, string $slug): JsonResponse
    {
        $publication = Publication::where('slug', $slug)->firstOrFail();
        $publication->recordEvent('view');

        return response()->json([
            'success' => true,
        ]);
    }

    public function recordShare(Request $request, string $slug): JsonResponse
    {
        $publication = Publication::where('slug', $slug)->firstOrFail();
        $publication->recordEvent('share');

        return response()->json([
            'success' => true,
        ]);
    }

    public function stats(Request $request, string $slug): JsonResponse
    {
        $publication = Publication::where('slug', $slug)->firstOrFail();
        $publication->load('stats');

        return response()->json([
            'success' => true,
            'data' => new PubStatisticResource($publication->stats),
        ]);
    }

    public function pdf(Request $request, string $slug)
    {
        $publication = Publication::where('slug', $slug)->firstOrFail();

        $html = Storage::disk('public')->get($publication->html_file_path);

        $pdf = Pdf::loadHTML($html);

        $publication->recordEvent('pdf_download');

        return $pdf->stream($slug . '.pdf');
    }
}
