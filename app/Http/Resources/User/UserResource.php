<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Tenant\TenantResource;
use App\Http\Resources\TenantUser\TenantUserResource;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    private ?Tenant $tenant = null;

    public function setTenant(?Tenant $tenant): self
    {
        $this->tenant = $tenant;

        return $this;
    }

    /**
     * Transform the resource into an array
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toAtomString(),
            'lang' => $this->lang,
            'timezone' => $this->timezone,
            'lock_version' => $this->lock_version,
            'created_at' => $this->created_at?->toAtomString(),
            'updated_at' => $this->updated_at?->toAtomString(),
            'avatar_url' => $this->avatar_url,
        ];

        if ($this->tenant) {
            $data['tenant'] = new TenantResource($this->tenant);
            $data['tenant_user'] = new TenantUserResource($this->tenant->pivot);
        }

        return $data;
    }
}
