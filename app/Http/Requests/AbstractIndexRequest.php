<?php

namespace App\Http\Requests;

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
            'search_phrase' => ['nullable', 'string', 'max:255'],
            'sort' => [
                'nullable',
                'string',
                'regex:/^('.implode('|', $this->getAllowedSortAttributes()).')\|(asc|desc)$/',
            ],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
