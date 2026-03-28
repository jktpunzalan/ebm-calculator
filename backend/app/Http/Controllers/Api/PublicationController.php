<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Publication\RecordEventRequest;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use App\Models\PubEvent;
use App\Models\PubStatistic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Publication::query()
            ->where('is_active', true)
            ->with('stats')
            ->latest('published_at');

        if ($type = $request->query('type')) {
            $query->where('study_type', $type);
        }

        $paginator = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => PublicationResource::collection($paginator->items()),
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $pub = Publication::where('slug', $slug)
            ->where('is_active', true)
            ->with('stats')
            ->first();

        if (! $pub) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PublicationResource($pub),
        ]);
    }

    public function stats(string $slug): JsonResponse
    {
        $pub = Publication::where('slug', $slug)->where('is_active', true)->first();
        if (! $pub) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $stats = PubStatistic::firstOrCreate(['publication_id' => $pub->id]);

        return response()->json([
            'success' => true,
            'data' => [
                'views' => $stats->views,
                'shares' => $stats->shares,
                'pdf_downloads' => $stats->pdf_downloads,
                'saves' => $stats->saves,
            ],
        ]);
    }

    public function recordView(Request $request, string $slug): JsonResponse
    {
        $pub = Publication::where('slug', $slug)->where('is_active', true)->first();
        if (! $pub) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        PubEvent::create([
            'publication_id' => $pub->id,
            'event_type' => 'view',
            'user_agent' => $request->header('User-Agent'),
            'ip_hash' => hash('sha256', $request->ip()),
            'occurred_at' => Carbon::now(),
        ]);

        PubStatistic::where('publication_id', $pub->id)->increment('views');

        return response()->json(['success' => true]);
    }

    public function recordEvent(RecordEventRequest $request, string $slug): JsonResponse
    {
        $pub = Publication::where('slug', $slug)->where('is_active', true)->first();
        if (! $pub) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $eventType = $request->validated()['event_type'];

        PubEvent::create([
            'publication_id' => $pub->id,
            'event_type' => $eventType,
            'user_agent' => $request->header('User-Agent'),
            'ip_hash' => hash('sha256', $request->ip()),
            'occurred_at' => Carbon::now(),
        ]);

        $column = match ($eventType) {
            'share' => 'shares',
            'pdf' => 'pdf_downloads',
            'save' => 'saves',
        };

        PubStatistic::where('publication_id', $pub->id)->increment($column);

        return response()->json(['success' => true]);
    }
}
