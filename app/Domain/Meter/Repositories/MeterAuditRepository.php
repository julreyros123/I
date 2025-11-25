<?php

namespace App\Domain\Meter\Repositories;

interface MeterAuditRepository
{
    public function created(int $meterId, ?int $userId, array $to): void;
}
