<?php

namespace App\Application\Meter\DTO;

class CreateMeterCommand
{
    public function __construct(public array $data) {}
}
