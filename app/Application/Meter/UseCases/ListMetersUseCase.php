<?php

namespace App\Application\Meter\UseCases;

use App\Application\Meter\DTO\ListMetersQuery;
use App\Domain\Meter\Repositories\MeterRepository;

class ListMetersUseCase
{
    public function __construct(private MeterRepository $meters) {}

    public function handle(ListMetersQuery $query): array
    {
        $paginator = $this->meters->paginateWithFilters($query->q, $query->status, $query->type, $query->barangay, $query->scope, $query->perPage);
        $statuses = ['inventory','installed','active','maintenance','inactive','retired'];
        $kpis = $this->meters->statusCounts();
        $eligibleStatuses = collect(['inventory', 'installed', 'active'])->mapWithKeys(fn($s) => [$s => $kpis[$s] ?? 0]);
        return [
            'meters' => $paginator,
            'statuses' => $statuses,
            'kpis' => collect($kpis),
            'eligibleCounts' => $eligibleStatuses,
        ];
    }
}
