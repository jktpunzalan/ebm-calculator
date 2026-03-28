<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfessionalItemResource;
use App\Models\ProfessionalItem;
use Illuminate\Http\JsonResponse;

class ProfessionalController extends Controller
{
    public function index(): JsonResponse
    {
        $items = ProfessionalItem::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProfessionalItemResource::collection($items),
        ]);
    }
}
