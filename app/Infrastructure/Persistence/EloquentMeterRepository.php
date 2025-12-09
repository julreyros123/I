<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Meter\Repositories\MeterRepository;
use App\Models\Meter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentMeterRepository implements MeterRepository
{
    public function paginateWithFilters(?string $q, ?string $status, ?string $type, ?string $barangay, string $scope = 'eligible', int $perPage = 15): LengthAwarePaginator
    {
        $query = Meter::query()->with(['currentCustomer.latestApplication']);
        if ($scope === 'eligible') {
            $query->whereIn('status', ['inventory', 'installed', 'active']);
        }
        if ($q) {
            $query->where(function($qq) use ($q) {
                $qq->where('serial','like','%'.$q.'%')
                    ->orWhere('location_address','like','%'.$q.'%')
                    ->orWhere('barangay','like','%'.$q.'%');
            });
        }
        if ($status) { $query->where('status',$status); }
        if ($type) { $query->where('type',$type); }
        if ($barangay) { $query->where('barangay',$barangay); }
        return $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function statusCounts(): array
    {
        return Meter::select('status', DB::raw('count(*) as c'))
            ->groupBy('status')->pluck('c','status')->toArray();
    }

    public function create(array $data): object
    {
        return Meter::create($data);
    }
}
