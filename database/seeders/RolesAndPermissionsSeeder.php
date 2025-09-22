<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure default roles exist
        foreach (['Super Admin', 'Admin', 'Accountant', 'Member'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }
}
