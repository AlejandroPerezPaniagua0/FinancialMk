<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\HistoricalPriceRepositoryInterface;
use App\Repositories\Interfaces\InstrumentRepositoryInterface;
use App\Services\Interfaces\ExportServiceInterface;
use App\Services\Interfaces\WatchlistServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Universal export endpoints. The pillar 4 promise: "your data is yours."
 *
 *   • /api/export/instruments/{id}/prices  · CSV/JSON of historical OHLCV
 *   • /api/export/watchlists/{id}          · CSV/JSON of a watchlist
 *
 * Format is selected by the `format` query param (csv|json), defaulting
 * to csv because that's what most users will paste into a spreadsheet.
 */
class ExportController extends Controller
{
    public function __construct(
        private readonly ExportServiceInterface $exporter,
        private readonly InstrumentRepositoryInterface $instruments,
        private readonly HistoricalPriceRepositoryInterface $prices,
        private readonly WatchlistServiceInterface $watchlists,
    ) {}

    public function instrumentPrices(Request $request, int $id): StreamedResponse|JsonResponse
    {
        $instrument = $this->instruments->findById($id);
        if (! $instrument) {
            return response()->json(['message' => 'Instrument not found.'], 404);
        }

        $validated = $request->validate([
            'from' => 'sometimes|date_format:Y-m-d',
            'to' => 'sometimes|date_format:Y-m-d|after_or_equal:from',
            'format' => 'sometimes|in:csv,json',
        ]);

        $rows = $this->prices->getByInstrument(
            $id,
            $validated['from'] ?? null,
            $validated['to'] ?? null,
        );

        $format = $validated['format'] ?? 'csv';
        $filename = sprintf('%s_prices_%s.%s', $instrument->ticker, now()->format('Ymd'), $format);

        if ($format === 'json') {
            return $this->exporter->json(
                $rows->map(fn ($r) => [
                    'date' => (string) $r->date,
                    'open' => (float) $r->open,
                    'high' => (float) $r->high,
                    'low' => (float) $r->low,
                    'close' => (float) $r->close,
                    'adjusted_close' => (float) $r->adjusted_close,
                    'volume' => (int) $r->volume,
                ])->all(),
                $filename,
            );
        }

        return $this->exporter->csv(
            ['date', 'open', 'high', 'low', 'close', 'adjusted_close', 'volume'],
            $rows->map(fn ($r) => [
                'date' => (string) $r->date,
                'open' => (string) $r->open,
                'high' => (string) $r->high,
                'low' => (string) $r->low,
                'close' => (string) $r->close,
                'adjusted_close' => (string) $r->adjusted_close,
                'volume' => (string) $r->volume,
            ])->all(),
            $filename,
        );
    }

    public function watchlist(Request $request, int $id): StreamedResponse|JsonResponse
    {
        $watchlist = $this->watchlists->show($request->user(), $id);
        if (! $watchlist) {
            return response()->json(['message' => 'Watchlist not found.'], 404);
        }

        $validated = $request->validate([
            'format' => 'sometimes|in:csv,json',
        ]);

        $format = $validated['format'] ?? 'csv';
        $filename = sprintf('watchlist_%d_%s.%s', $id, now()->format('Ymd'), $format);

        if ($format === 'json') {
            return $this->exporter->json([$watchlist->toArray()], $filename);
        }

        return $this->exporter->csv(
            ['ticker', 'name', 'asset_class', 'currency', 'position'],
            $watchlist->instruments,
            $filename,
        );
    }
}
