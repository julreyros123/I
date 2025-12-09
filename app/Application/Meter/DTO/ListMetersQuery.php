<?php

namespace App\Application\Meter\DTO;

class ListMetersQuery
{
    public function __construct(
        public readonly ?string $q = null,
        public readonly ?string $status = null,
        public readonly ?string $type = null,
        public readonly ?string $barangay = null,
        public readonly int $perPage = 15,
        public readonly string $scope = 'eligible',
    ) {}
}
