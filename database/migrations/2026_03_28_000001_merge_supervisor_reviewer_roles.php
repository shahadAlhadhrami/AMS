<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $newRole = Role::firstOrCreate(['name' => 'Reviewer/Supervisor', 'guard_name' => 'web']);

        foreach (['Supervisor', 'Reviewer'] as $oldRoleName) {
            $oldRole = Role::where('name', $oldRoleName)->where('guard_name', 'web')->first();

            if (! $oldRole) {
                continue;
            }

            // Migrate all users who have the old role to the new role
            $userIds = \DB::table('model_has_roles')
                ->where('role_id', $oldRole->id)
                ->where('model_type', 'App\\Models\\User')
                ->pluck('model_id');

            foreach ($userIds as $userId) {
                $alreadyHasNewRole = \DB::table('model_has_roles')
                    ->where('role_id', $newRole->id)
                    ->where('model_type', 'App\\Models\\User')
                    ->where('model_id', $userId)
                    ->exists();

                if (! $alreadyHasNewRole) {
                    \DB::table('model_has_roles')->insert([
                        'role_id'    => $newRole->id,
                        'model_type' => 'App\\Models\\User',
                        'model_id'   => $userId,
                    ]);
                }
            }

            // Remove old role assignments and delete old role
            \DB::table('model_has_roles')->where('role_id', $oldRole->id)->delete();
            $oldRole->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);
        $reviewerRole   = Role::firstOrCreate(['name' => 'Reviewer', 'guard_name' => 'web']);

        $newRole = Role::where('name', 'Reviewer/Supervisor')->where('guard_name', 'web')->first();

        if ($newRole) {
            $userIds = \DB::table('model_has_roles')
                ->where('role_id', $newRole->id)
                ->where('model_type', 'App\\Models\\User')
                ->pluck('model_id');

            foreach ($userIds as $userId) {
                foreach ([$supervisorRole->id, $reviewerRole->id] as $roleId) {
                    $exists = \DB::table('model_has_roles')
                        ->where('role_id', $roleId)
                        ->where('model_type', 'App\\Models\\User')
                        ->where('model_id', $userId)
                        ->exists();

                    if (! $exists) {
                        \DB::table('model_has_roles')->insert([
                            'role_id'    => $roleId,
                            'model_type' => 'App\\Models\\User',
                            'model_id'   => $userId,
                        ]);
                    }
                }
            }

            \DB::table('model_has_roles')->where('role_id', $newRole->id)->delete();
            $newRole->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
