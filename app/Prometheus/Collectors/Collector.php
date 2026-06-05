<?php

namespace App\Prometheus\Collectors;

interface Collector
{
    public function register(): void;
}
