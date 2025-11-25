<?php

namespace App\Application\Meter\UseCases;

use App\Application\Meter\DTO\ListMetersQuery;
use App\Domain\Meter\Repositories\MeterRepository;

class ListMetersUseCase
{
    public function __construct(private MeterRepository $meters) {}

    public function handle(ListMetersQuery $query): array
    {
        $paginator = $this->meters->paginateWithFilters($query->q, $query->status, $query->type, $query->barangay, $query->perPage);
        $statuses = ['inventory','installed','active','maintenance','inactive','retired'];
        $kpis = $this->meters->statusCounts();
        return [
            'meters' => $paginator,
            'statuses' => $statuses,
            'kpis' => collect($kpis),
        ];
    }
}
