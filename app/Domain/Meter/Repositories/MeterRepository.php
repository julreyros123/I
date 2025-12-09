<?php

namespace App\Domain\Meter\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MeterRepository
{
    public function paginateWithFilters(
        ?string $q,
        ?string $status,
        ?string $type,
        ?string $barangay,
        string $scope = 'eligible',
        int $perPage = 15,
    ): LengthAwarePaginator;

    public function statusCounts(): array;

    public function create(array $data): object; // returns Meter model/entity
}
