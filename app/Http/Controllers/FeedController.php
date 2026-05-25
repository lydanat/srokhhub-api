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
        $source = $request->query('source'); // optional filter e.g. ?source=bbc
        $perPage = (int) $request->query('per_page', 20);

        $feed = $this->feedService->getUnifiedFeed($perPage, $source);

        return response()->json($feed);
    }
}