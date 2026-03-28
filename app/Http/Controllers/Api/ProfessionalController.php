<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfessionalItemResource;
use App\Models\ProfessionalItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = ProfessionalItem::orderBy('display_order')->get();

        return response()->json([
            'success' => true,
            'data' => ProfessionalItemResource::collection($items),
        ]);
    }
}
