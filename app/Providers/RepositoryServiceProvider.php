<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Meter\Repositories\MeterRepository;
use App\Domain\Meter\Repositories\MeterAuditRepository;
use App\Infrastructure\Persistence\EloquentMeterRepository;
use App\Infrastructure\Persistence\EloquentMeterAuditRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MeterRepository::class, EloquentMeterRepository::class);
        $this->app->bind(MeterAuditRepository::class, EloquentMeterAuditRepository::class);
    }
}
