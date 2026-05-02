<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\WatchlistServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thin REST surface over WatchlistService. All endpoints are scoped to the
 * authenticated user — the service only ever loads watchlists where
 * user_id = auth user, so a malicious id in the URL returns 404 (never
 * leaks another user's data).
 */
class WatchlistController extends Controller
{
    public function __construct(private readonly WatchlistServiceInterface $watchlists) {}

    public function index(Request $request): JsonResponse
    {
        $list = $this->watchlists->listFor($request->user());
        return response()->json([
            'data' => array_map(fn ($w) => $w->toArray(), $list),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $watchlist = $this->watchlists->show($request->user(), $id);
        if (! $watchlist) {
            return response()->json(['message' => 'Watchlist not found.'], 404);
        }

        return response()->json(['data' => $watchlist->toArray()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:120',
            'is_default' => 'nullable|boolean',
        ]);

        $created = $this->watchlists->create(
            $request->user(),
            $validated['name'],
            (bool) ($validated['is_default'] ?? false),
        );

        return response()->json(['data' => $created->toArray()], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $renamed = $this->watchlists->rename($request->user(), $id, $validated['name']);
        if (! $renamed) {
            return response()->json(['message' => 'Watchlist not found.'], 404);
        }

        return response()->json(['data' => $renamed->toArray()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = $this->watchlists->delete($request->user(), $id);
        if (! $deleted) {
            return response()->json(['message' => 'Watchlist not found.'], 404);
        }

        return response()->json(['message' => 'Watchlist deleted.']);
    }

    /**
     * Replace the instruments in this watchlist with the provided list.
     * Idempotent — re-sending the same array is a no-op.
     */
    public function syncInstruments(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'instrument_ids'   => 'required|array',
            'instrument_ids.*' => 'integer|exists:instruments,id',
        ]);

        $synced = $this->watchlists->setInstruments(
            $request->user(),
            $id,
            $validated['instrument_ids'],
        );

        if (! $synced) {
            return response()->json(['message' => 'Watchlist not found.'], 404);
        }

        return response()->json(['data' => $synced->toArray()]);
    }
}
