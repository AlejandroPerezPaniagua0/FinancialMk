<?php

namespace App\Services;

use App\Services\Interfaces\ExportServiceInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streaming exporters used by ExportController. Streams the response body
 * line-by-line so a 10-year history of 50 tickers doesn't OOM the worker.
 *
 * Headers force a download: `Content-Disposition: attachment; filename=...`.
 */
class ExportService implements ExportServiceInterface
{
    public function csv(array $headers, iterable $rows, string $filename): StreamedResponse
    {
        return new StreamedResponse(
            function () use ($headers, $rows): void {
                $stream = fopen('php://output', 'wb');
                fputcsv($stream, $headers);
                foreach ($rows as $row) {
                    $line = [];
                    foreach ($headers as $header) {
                        $value = $row[$header] ?? '';
                        $line[] = is_scalar($value) || $value === null
                            ? (string) $value
                            : json_encode($value);
                    }
                    fputcsv($stream, $line);
                }
                fclose($stream);
            },
            200,
            $this->headers('text/csv; charset=UTF-8', $filename),
        );
    }

    public function json(iterable $rows, string $filename): StreamedResponse
    {
        return new StreamedResponse(
            function () use ($rows): void {
                echo '[';
                $first = true;
                foreach ($rows as $row) {
                    if (! $first) {
                        echo ',';
                    }
                    echo json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    $first = false;
                }
                echo ']';
            },
            200,
            $this->headers('application/json; charset=UTF-8', $filename),
        );
    }

    /** @return array<string, string> */
    private function headers(string $contentType, string $filename): array
    {
        // ASCII fallback + RFC 5987 utf8 filename for clients that respect it.
        $safe = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $filename) ?? 'export';
        $utf8 = rawurlencode($filename);

        return [
            'Content-Type' => $contentType,
            'Content-Disposition' => "attachment; filename=\"{$safe}\"; filename*=UTF-8''{$utf8}",
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];
    }
}
