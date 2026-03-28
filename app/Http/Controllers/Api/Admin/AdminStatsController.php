<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use App\Models\PubStatistic;
use App\Models\PubEvent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminStatsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $totalPublications = Publication::active()->count();
        $totalUsers = User::count();
        $totalViews = PubStatistic::sum('views');
        $totalShares = PubStatistic::sum('shares');
        $totalPdfDownloads = PubStatistic::sum('pdf_downloads');

        $viewsThisMonth = PubEvent::where('event_type', 'view')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $topPublications = Publication::select('publications.*')
            ->join('pub_statistics', 'publications.id', '=', 'pub_statistics.publication_id')
            ->orderByDesc('pub_statistics.views')
            ->limit(5)
            ->with('stats')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_publications' => $totalPublications,
                'total_users' => $totalUsers,
                'total_views' => (int) $totalViews,
                'total_shares' => (int) $totalShares,
                'total_pdf_downloads' => (int) $totalPdfDownloads,
                'views_this_month' => $viewsThisMonth,
                'top_publications' => PublicationResource::collection($topPublications),
            ],
        ]);
    }
}
