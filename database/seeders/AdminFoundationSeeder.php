<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminPermission;
use App\Models\Admin\AdminRole;
use App\Models\Admin\BlogCategory;
use App\Models\Admin\Deity;
use App\Models\Admin\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['Manage all admins', 'manage-admins', 'Admins'],
            ['Manage roles', 'manage-roles', 'Admins'],
            ['Manage permissions', 'manage-permissions', 'Admins'],
            ['View users', 'view-users', 'Users'],
            ['Manage services', 'manage-services', 'Services'],
            ['Manage diyas', 'manage-diyas', 'Services'],
            ['Manage service categories', 'manage-service-categories', 'Services'],
            ['Manage deities', 'manage-deities', 'Services'],
            ['View bookings', 'view-bookings', 'Bookings'],
            ['Update bookings', 'update-bookings', 'Bookings'],
            ['View donations', 'view-donations', 'Finance'],
            ['Export donations', 'export-donations', 'Finance'],
            ['Manage audio', 'manage-audio', 'Audio'],
            ['Manage playlists', 'manage-playlists', 'Audio'],
            ['Manage blogs', 'manage-blogs', 'Content'],
            ['View notifications', 'view-notifications', 'Notifications'],
            ['View reports', 'view-reports', 'Reports'],
            ['Manage settings', 'manage-settings', 'Settings'],
            ['Manage live events', 'manage-live-events', 'Streaming'],
            ['Manage marketing', 'manage-marketing', 'Marketing'],
            ['Support bookings', 'support-bookings', 'Support'],
        ];

        $permissionIds = collect($permissions)->mapWithKeys(function (array $permission) {
            $model = AdminPermission::updateOrCreate(
                ['slug' => $permission[1]],
                ['name' => $permission[0], 'module' => $permission[2], 'description' => $permission[0]]
            );

            return [$permission[1] => $model->id];
        });

        $roles = [
            'super-admin' => ['Super Admin', 'Full control of the platform.', 'active', $permissionIds->keys()->all()],
            'temple-admin' => ['Temple Admin', 'Handles pooja, hawan, diya, and live spiritual sessions.', 'inactive', ['view-bookings', 'update-bookings', 'manage-live-events', 'manage-diyas']],
            'content-admin' => ['Content Admin', 'Handles content, services, SEO, FAQs, and festivals.', 'active', ['manage-blogs', 'manage-services', 'manage-diyas', 'manage-service-categories', 'manage-deities']],
            'audio-admin' => ['Audio Admin', 'Handles mantra, aarti, and ambience audio.', 'inactive', ['manage-audio', 'manage-playlists']],
            'streaming-admin' => ['Streaming Admin', 'Future-ready live pooja and hawan streams role.', 'inactive', ['manage-live-events', 'view-bookings']],
            'support-admin' => ['Support Admin', 'Handles users and booking support.', 'inactive', ['view-users', 'view-bookings', 'support-bookings']],
            'finance-admin' => ['Finance Admin', 'Handles payments, donations, receipts, and reports.', 'active', ['view-donations', 'export-donations', 'view-reports']],
            'marketing-admin' => ['Marketing Admin', 'Handles banners, campaigns, and promotions.', 'inactive', ['manage-marketing', 'view-reports']],
        ];

        foreach ($roles as $slug => [$name, $description, $status, $rolePermissions]) {
            $role = AdminRole::updateOrCreate(
                ['slug' => $slug],
                compact('name', 'description', 'status')
            );

            $role->permissions()->sync(collect($rolePermissions)->map(fn ($slug) => $permissionIds[$slug])->all());
        }

        $superRole = AdminRole::where('slug', 'super-admin')->firstOrFail();

        Admin::updateOrCreate(
            ['email' => 'admin@bhaktideep.com'],
            [
                'name' => 'BhaktiDeep Super Admin',
                'mobile' => '9999999999',
                'password' => Hash::make('BhaktiDeep@123'),
                'role_id' => $superRole->id,
                'status' => 'active',
            ]
        );

        foreach (['Diya', 'Pooja', 'Hawan'] as $category) {
            ServiceCategory::updateOrCreate(
                ['slug' => str($category)->slug()->toString()],
                ['name' => $category, 'status' => 'active']
            );
        }

        foreach (['Shiv', 'Lakshmi', 'Hanuman', 'Ganesh'] as $deity) {
            Deity::updateOrCreate(
                ['slug' => str($deity)->slug()->toString()],
                ['name' => $deity, 'status' => 'active']
            );
        }

        BlogCategory::updateOrCreate(
            ['slug' => 'festivals'],
            ['name' => 'Festivals', 'status' => 'active']
        );

        foreach (['Pooja', 'Hawan', 'Diya', 'Live Aarti', 'Sankalp', 'Spiritual Guides'] as $cat) {
            BlogCategory::updateOrCreate(
                ['slug' => str($cat)->slug()->toString()],
                ['name' => $cat, 'status' => 'active']
            );
        }
    }
}
