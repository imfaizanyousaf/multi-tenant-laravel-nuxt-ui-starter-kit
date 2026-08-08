<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use function now;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        $admin = User::query()->firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin User',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $admin->assignRole(Role::SUPER_ADMIN);
    }
}
