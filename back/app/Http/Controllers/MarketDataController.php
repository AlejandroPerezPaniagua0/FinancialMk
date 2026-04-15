<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssetClassResource;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\HistoricalPriceResource;
use App\Http\Resources\InstrumentResource;
use App\Models\AssetClass;
use App\Models\Currency;
use App\Repositories\Interfaces\HistoricalPriceRepositoryInterface;
use App\Repositories\Interfaces\InstrumentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarketDataController extends Controller
{
    public function __construct(
        private readonly InstrumentRepositoryInterface $instrumentRepository,
        private readonly HistoricalPriceRepositoryInterface $historicalPriceRepository,
    ) {}

    /**
     * GET /api/asset-classes
     * Returns all asset classes.
     */
    public function assetClasses(): AnonymousResourceCollection
    {
        return AssetClassResource::collection(AssetClass::all());
    }

    /**
     * GET /api/currencies
     * Returns all currencies.
     */
    public function currencies(): AnonymousResourceCollection
    {
        return CurrencyResource::collection(Currency::all());
    }

    /**
     * GET /api/instruments
     * Returns a paginated list of instruments.
     *
     * Query params:
     *   asset_class_id (int, optional) — filter by asset class
     *   per_page       (int, optional, default 20)
     */
    public function instruments(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'asset_class_id' => 'sometimes|integer|exists:asset_classes,id',
            'per_page'       => 'sometimes|integer|min:1|max:100',
        ]);

        $paginator = $this->instrumentRepository->paginate($validated);

        return InstrumentResource::collection($paginator);
    }

    /**
     * GET /api/instruments/{id}/prices
     * Returns historical prices for a given instrument.
     *
     * Query params:
     *   from (date Y-m-d, optional)
     *   to   (date Y-m-d, optional)
     */
    public function prices(Request $request, int $id): AnonymousResourceCollection|JsonResponse
    {
        $instrument = $this->instrumentRepository->findById($id);

        if ($instrument === null) {
            return response()->json(['message' => 'Instrument not found.'], 404);
        }

        $validated = $request->validate([
            'from' => 'sometimes|date_format:Y-m-d',
            'to'   => 'sometimes|date_format:Y-m-d|after_or_equal:from',
        ]);

        $prices = $this->historicalPriceRepository->getByInstrument(
            $id,
            $validated['from'] ?? null,
            $validated['to'] ?? null,
        );

        return HistoricalPriceResource::collection($prices);
    }
}
