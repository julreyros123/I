<?php

namespace App\Application\Meter\UseCases;

use App\Application\Meter\DTO\CreateMeterCommand;
use App\Domain\Meter\Repositories\MeterAuditRepository;
use App\Domain\Meter\Repositories\MeterRepository;

class CreateMeterUseCase
{
    public function __construct(
        private MeterRepository $meters,
        private MeterAuditRepository $audits,
    ) {}

    public function handle(CreateMeterCommand $cmd, ?int $userId = null): object
    {
        $meter = $this->meters->create($cmd->data);
        $this->audits->created($meter->id, $userId, (array) $meter->toArray());
        return $meter;
    }
}
