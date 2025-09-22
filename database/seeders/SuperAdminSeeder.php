<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Super Admin credentials
        $name = env('SUPERADMIN_NAME', 'Super Admin');
        $email = env('SUPERADMIN_EMAIL', 'admin@example.com');
        $password = env('SUPERADMIN_PASSWORD', 'password');

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password]
        );

        // Ensure roles exist (seeded by RolesAndPermissionsSeeder) and assign
        if (! $user->hasRole('Admin')) {
            $user->assignRole('Admin');
        }
        if (! $user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }
    }
}
