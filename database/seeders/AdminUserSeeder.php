<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * GR-202: مدیر اولیه — رمز پیش‌فرض باید پس از نخستین ورود تغییر کند.
     * در استیجینگ/تولید مقدار ADMIN_INITIAL_PASSWORD از env خوانده می‌شود.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['mobile' => env('ADMIN_INITIAL_MOBILE', '09120000000')],
            [
                'name' => env('ADMIN_INITIAL_NAME', 'مدیر آزمایشگاه'),
                'password' => env('ADMIN_INITIAL_PASSWORD', 'change-me-2026'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
