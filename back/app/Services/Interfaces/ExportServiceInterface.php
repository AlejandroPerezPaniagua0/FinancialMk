<?php

namespace App\Services\Interfaces;

use Symfony\Component\HttpFoundation\StreamedResponse;

interface ExportServiceInterface
{
    /**
     * Stream the given rows as CSV with the provided headers.
     *
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<string, mixed>>  $rows  Each row keyed by header.
     */
    public function csv(array $headers, iterable $rows, string $filename): StreamedResponse;

    /**
     * Stream a JSON array document. Pass an iterable so very large datasets
     * don't have to materialize in memory.
     *
     * @param  iterable<int, mixed>  $rows
     */
    public function json(iterable $rows, string $filename): StreamedResponse;
}
