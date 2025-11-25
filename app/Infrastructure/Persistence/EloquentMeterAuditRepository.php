<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Meter\Repositories\MeterAuditRepository;
use App\Models\MeterAudit;

class EloquentMeterAuditRepository implements MeterAuditRepository
{
    public function created(int $meterId, ?int $userId, array $to): void
    {
        MeterAudit::create([
            'meter_id' => $meterId,
            'action' => 'create',
            'changed_by' => $userId,
            'to_json' => $to,
        ]);
    }
}
