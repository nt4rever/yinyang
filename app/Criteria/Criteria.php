<?php

namespace App\Criteria;

class Criteria
{
    public function __construct(
        public array $filters = [],
        public ?string $sortAttribute = null,
        public ?string $sortDirection = 'desc',
        public int $limit = 15,
    ) {}
}
