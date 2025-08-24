<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;

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

    /**
     * Sort by column and direction
     */
    public function sort(string $sort): self
    {
        $sortCol = explode('|', $sort);

        if (! is_array($sortCol) || count($sortCol) != 2) {
            return $this;
        }

        $dir = ($sortCol[1] == 'asc') ? 'asc' : 'desc';

        if ($sortCol[0] == 'name') {
            return $this->orderByRaw("LOWER(name) {$dir}");
        }

        // Sort by number
        // if ($sortCol[0] == 'number') {
        //     return $this->orderByRaw("REGEXP_REPLACE(number,'[^0-9]+','')+0 " . $dir);
        // }

        return $this->orderBy($sortCol[0], $dir);
    }

    /**
     * Filter by created_at_from
     */
    public function createdAtFrom(string $value): self
    {
        try {
            $created_at = is_numeric($value)
                ? Carbon::createFromTimestamp((int) $value)
                : Carbon::parse($value);

            return $this->where('created_at', '>=', $created_at);
        } catch (\Exception $e) {
            return $this;
        }
    }

    /**
     * Filter by created_at_to
     */
    public function createdAtTo(string $value): self
    {
        try {
            $created_at = is_numeric($value)
                ? Carbon::createFromTimestamp((int) $value)
                : Carbon::parse($value);

            return $this->where('created_at', '<=', $created_at);
        } catch (\Exception $e) {
            return $this;
        }
    }
}
