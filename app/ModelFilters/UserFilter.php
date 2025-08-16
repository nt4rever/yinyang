<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function searchPhrase(string $searchPhrase): self
    {
        return $this->where(function ($q) use ($searchPhrase) {
            return $q->where('name', 'LIKE', "%$searchPhrase%")
                ->orWhere('email', 'LIKE', "%$searchPhrase%");
        });
    }

    /**
     * Filter by email
     */
    public function email(string $email): self
    {
        return $this->where('email', $email);
    }

    /**
     * Filter by name
     */
    public function name(string $name): self
    {
        return $this->where('name', $name);
    }
}
