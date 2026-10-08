<?php

namespace Database\Seeders;

use App\Domain\Platform\Models\PlatformUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformAdminSeeder extends Seeder
{
    public function run(): void
    {
        PlatformUser::firstOrCreate(
            ['email' => 'admin@erplannet.com'],
            [
                'name' => 'Platform Superadmin',
                'password' => Hash::make('SuperSecurePass123!'),
                'role' => 'superadmin',
                'is_active' => true,
            ]
        );
    }
}
