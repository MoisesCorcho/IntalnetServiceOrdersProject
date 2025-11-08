<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Execute the php artisan shield:generate --all command
        Artisan::call('shield:generate', ['--all' => true, '--panel' => 'admin']);

        $adminUser = User::where('email', 'like', '%admin@admin.com%')->first();

        // Execute the command php artisan shield:super-admin to create the super_admin role
        Artisan::call('shield:super-admin', ['--user' => $adminUser->id]);

        // Assign only the Service Order view/update permissions to technician
        $technicianPermissions = Permission::query()
            ->whereIn('name', [
                'view_service::order',
                'view_any_service::order',
                'update_service::order',
            ])
            ->get();
        $adminPermissions = Permission::all();

        $admin = Role::where('name', 'super_admin')->first();
        $technician = Role::where('name', 'tecnico')->first();

        // Assign permissions to roles
        $admin->syncPermissions($adminPermissions);
        $technician->syncPermissions($technicianPermissions);

        $this->command->info('Roles and permissions have been created successfully.');
    }
}
