<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journal\StoreJournalRequest;
use App\Http\Requests\Journal\UpdateJournalRequest;
use App\Http\Requests\Publication\StorePublicationRequest;
use App\Http\Requests\Publication\UpdatePublicationRequest;
use App\Http\Requests\Professional\StoreProfessionalRequest;
use App\Http\Requests\Professional\UpdateProfessionalRequest;
use App\Http\Resources\PublicationResource;
use App\Http\Resources\JournalArticleResource;
use App\Http\Resources\ProfessionalItemResource;
use App\Models\JournalArticle;
use App\Models\ProfessionalItem;
use App\Models\Publication;
use App\Models\PubEvent;
use App\Models\PubStatistic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function stats(): JsonResponse
    {
        $now = Carbon::now();
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        $totalPublications = Publication::count();
        $totalJournal = JournalArticle::count();
        $totalUsers = User::count();

        $totals = PubStatistic::whereBetween('updated_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(views),0) as views, COALESCE(SUM(shares),0) as shares')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_publications' => $totalPublications,
                'total_views_this_month' => (int)($totals->views ?? 0),
                'total_shares_this_month' => (int)($totals->shares ?? 0),
                'total_journal_articles' => $totalJournal,
                'total_users' => $totalUsers,
            ],
        ]);
    }

    public function store(StorePublicationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $publication = Publication::create($data);
        PubStatistic::create(['publication_id' => $publication->id]);

        return response()->json([
            'success' => true,
            'data' => new PublicationResource($publication->load('stats')),
        ], 201);
    }

    public function update(UpdatePublicationRequest $request, int $id): JsonResponse
    {
        $publication = Publication::findOrFail($id);
        $publication->update($request->validated());
        return response()->json([
            'success' => true,
            'data' => new PublicationResource($publication->load('stats')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $publication = Publication::findOrFail($id);
        PubStatistic::where('publication_id', $publication->id)->delete();
        PubEvent::where('publication_id', $publication->id)->delete();
        $publication->delete();
        return response()->json(['success' => true]);
    }

    public function storeArticle(StoreJournalRequest $request): JsonResponse
    {
        $article = JournalArticle::create($request->validated());
        return response()->json(['success' => true, 'data' => new JournalArticleResource($article)], 201);
    }

    public function updateArticle(UpdateJournalRequest $request, int $id): JsonResponse
    {
        $article = JournalArticle::findOrFail($id);
        $article->update($request->validated());
        return response()->json(['success' => true, 'data' => new JournalArticleResource($article)]);
    }

    public function destroyArticle(int $id): JsonResponse
    {
        $article = JournalArticle::findOrFail($id);
        $article->delete();
        return response()->json(['success' => true]);
    }

    public function storeProfessional(StoreProfessionalRequest $request): JsonResponse
    {
        $item = ProfessionalItem::create($request->validated());
        return response()->json(['success' => true, 'data' => new ProfessionalItemResource($item)], 201);
    }

    public function updateProfessional(UpdateProfessionalRequest $request, int $id): JsonResponse
    {
        $item = ProfessionalItem::findOrFail($id);
        $item->update($request->validated());
        return response()->json(['success' => true, 'data' => new ProfessionalItemResource($item)]);
    }

    public function destroyProfessional(int $id): JsonResponse
    {
        $item = ProfessionalItem::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }
}
