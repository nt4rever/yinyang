<?php

namespace App\Prometheus;

use Illuminate\Support\Facades\Facade;
use Prometheus\CollectorRegistry;

class Prom extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CollectorRegistry::class;
    }
}
