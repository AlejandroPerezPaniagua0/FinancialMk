<?php

namespace App\Repositories\Interfaces;

use App\Models\Instrument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InstrumentRepositoryInterface
{
    /**
     * Return a paginated list of instruments, optionally filtered by asset class.
     *
     * @param  array{asset_class_id?: int, per_page?: int}  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?Instrument;
}
