<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JournalArticleResource;
use App\Models\JournalArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $paginator = JournalArticle::query()
            ->orderByDesc('year')
            ->orderByDesc('published_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => JournalArticleResource::collection($paginator->items()),
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }
}
