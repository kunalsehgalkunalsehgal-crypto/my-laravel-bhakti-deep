<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\AdminPermission;

class AdminPermissionController extends BaseAdminResourceController
{
    protected string $modelClass = AdminPermission::class;
    protected string $routePrefix = 'admin.permissions';
    protected string $viewTitle = 'Permissions';
    protected string $permission = 'manage-permissions';
    protected array $search = ['name', 'slug', 'module'];
    protected array $fields = [
        'name' => ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'module' => ['label' => 'Module', 'rules' => ['required', 'string', 'max:255']],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
    ];
}
