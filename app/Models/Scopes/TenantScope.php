<?php

namespace App\Models\Scopes;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->runningInConsole() && ! Auth::check()) {
            return;
        }

        if (! Auth::check()) {
            throw new AuthenticationException('Authentication is required to use this model.');
        }

        $builder->where($model->qualifyColumn('tenant_id'), current_tenant()?->id);
    }
}
