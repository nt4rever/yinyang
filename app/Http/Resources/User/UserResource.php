<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Tenant\TenantResource;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    private ?Tenant $currentTenant = null;

    public function setCurrentTenant(?Tenant $tenant): self
    {
        $this->currentTenant = $tenant;

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

        if ($this->currentTenant) {
            $data['current_tenant'] = new TenantResource($this->currentTenant);
            $data['user_type'] = $this->currentTenant->pivot->type;
            $data['user_status'] = $this->currentTenant->pivot->status;
        }

        return $data;
    }
}
