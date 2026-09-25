<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_permissions')->updateOrInsert(
            ['slug' => 'manage-payouts'],
            [
                'name' => 'Manage payouts',
                'module' => 'Finance',
                'description' => 'Manage payouts',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $permissionId = DB::table('admin_permissions')->where('slug', 'manage-payouts')->value('id');
        $roleIds = DB::table('admin_roles')->whereIn('slug', ['super-admin', 'finance-admin'])->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('admin_role_permissions')->insertOrIgnore([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('admin_permissions')->where('slug', 'manage-payouts')->value('id');

        if ($permissionId) {
            DB::table('admin_role_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('admin_permissions')->where('id', $permissionId)->delete();
        }
    }
};
