<?php

namespace App\Http\Controllers;

use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function __construct(protected FeedService $feedService) {}

    public function index(Request $request): JsonResponse
    {
        $source = $request->query('source');
        $perPage = (int) $request->query('per_page', 20);
        $page = (int) $request->query('page', 1); 

        $feed = $this->feedService->getUnifiedFeed($perPage, $source, $page);

        return response()->json($feed);
    }
}