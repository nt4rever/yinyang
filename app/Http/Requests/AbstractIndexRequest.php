<?php

namespace App\Http\Requests;

use App\Criteria\Criteria;
use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractIndexRequest extends FormRequest
{
    /**
     * Get the allowed sort attributes
     */
    protected function getAllowedSortAttributes(): array
    {
        return [];
    }

    /**
     * Get the common rules
     */
    protected function commonRules(): array
    {
        return [
            'search_phrase' => 'nullable|string|max:255',
            'sort_attribute' => 'nullable|string|in:'.implode(',', $this->getAllowedSortAttributes()),
            'sort_direction' => 'nullable|string|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:1000',
        ];
    }

    /**
     * Get the criteria
     */
    public function getCriteria(): Criteria
    {
        return new Criteria(
            filters: $this->validated(),
            sortAttribute: $this->string('sort_attribute'),
            sortDirection: $this->string('sort_direction', 'desc'),
            limit: $this->integer('per_page', config('eloquentfilter.paginate_limit'))
        );
    }
}
