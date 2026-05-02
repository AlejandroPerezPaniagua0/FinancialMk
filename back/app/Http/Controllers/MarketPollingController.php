<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuoteResource;
use App\Services\Interfaces\MarketPollingServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketPollingController extends Controller
{
    public function __construct(
        private readonly MarketPollingServiceInterface $pollingService,
    ) {}

    /**
     * GET /api/instruments/quotes
     * Returns real-time quote snapshots for 1..max_assets instruments.
     *
     * Query params:
     *   ids (array<int>, required) — instrument ids to refresh.
     */
    public function quotes(Request $request): JsonResponse
    {
        $maxAssets = (int) config('market.polling.max_assets', 4);

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:'.$maxAssets],
            'ids.*' => ['integer', 'exists:instruments,id'],
        ]);

        $quotes = $this->pollingService->quotesFor($validated['ids']);

        return response()->json([
            'data' => QuoteResource::collection($quotes)->resolve(),
            'meta' => [
                'throttle_seconds' => (int) config('market.polling.throttle_seconds', 30),
                'max_assets' => $maxAssets,
                'server_time' => now()->toIso8601String(),
            ],
        ]);
    }
}
