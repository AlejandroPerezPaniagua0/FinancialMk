<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\InsightServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints that surface locally-computed risk/return statistics:
 *
 *   • GET /api/instruments/{id}/insights
 *   • GET /api/instruments/correlation-matrix?ids[]=1&ids[]=2
 *
 * No external network calls — everything is computed from the OHLCV we
 * already have in the database, which is the whole point of pillar 4
 * ("your data is yours").
 */
class InsightController extends Controller
{
    public function __construct(private readonly InsightServiceInterface $insights) {}

    public function show(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'benchmark' => 'sometimes|string|max:32',
        ]);

        $dto = $this->insights->forInstrument($id, $validated['benchmark'] ?? null);
        if (! $dto) {
            return response()->json(['message' => 'Instrument not found.'], 404);
        }

        return response()->json(['data' => $dto->toArray()]);
    }

    public function correlationMatrix(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:2',
            'ids.*' => 'integer|exists:instruments,id',
        ]);

        $matrix = $this->insights->correlationMatrix($validated['ids']);

        return response()->json(['data' => $matrix]);
    }
}
