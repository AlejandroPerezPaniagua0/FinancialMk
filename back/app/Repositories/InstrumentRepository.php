<?php

namespace App\Repositories;

use App\Models\Instrument;
use App\Repositories\Interfaces\InstrumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InstrumentRepository implements InstrumentRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Instrument::with(['assetClass', 'currency']);

        if (!empty($filters['asset_class_id'])) {
            $query->where('asset_class_id', $filters['asset_class_id']);
        }

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 20;

        return $query->orderBy('ticker')->paginate($perPage);
    }

    public function findById(int $id): ?Instrument
    {
        return Instrument::with(['assetClass', 'currency'])->find($id);
    }
}
