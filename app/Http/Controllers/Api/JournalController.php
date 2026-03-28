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
        $articles = JournalArticle::orderByDesc('year')
            ->orderByDesc('volume')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => JournalArticleResource::collection($articles),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }
}
