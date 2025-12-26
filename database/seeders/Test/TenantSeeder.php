<?php

namespace Database\Seeders\Test;

use App\Enums\TenantUserStatus;
use App\Enums\TenantUserType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tenant::factory()->count(10)->create()->each(function (Tenant $tenant) {
            $tenant->users()->attach(User::factory()->withLocalAccount()->create(), [
                'status' => TenantUserStatus::ACTIVE,
                'type' => TenantUserType::ADMIN,
            ]);
        });
    }
}
