<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'tickets.view',
            'tickets.view_any',
            'tickets.create',
            'tickets.assign',
            'tickets.close',
            'comments.create',
            'attachments.create',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $customer = Role::findOrCreate('customer', 'web');
        $customer->syncPermissions([
            'tickets.view',
            'tickets.create',
            'comments.create',
            'attachments.create',
        ]);

        $agent = Role::findOrCreate('agent', 'web');
        $agent->syncPermissions([
            'tickets.view',
            'tickets.view_any',
            'tickets.create',
            'tickets.assign',
            'tickets.close',
            'comments.create',
            'attachments.create',
        ]);

        $supervisor = Role::findOrCreate('supervisor', 'web');
        $supervisor->syncPermissions([
            'tickets.view',
            'tickets.view_any',
            'tickets.create',
            'comments.create',
            'attachments.create',
        ]);

        $admin = Role::findOrCreate('admin', 'web');
        $admin->syncPermissions(Permission::all());
    }
}