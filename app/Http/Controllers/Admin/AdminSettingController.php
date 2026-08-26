<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\PlatformSetting;

class AdminSettingController extends BaseAdminResourceController
{
    protected string $modelClass = PlatformSetting::class;
    protected string $routePrefix = 'admin.settings';
    protected string $viewTitle = 'Settings';
    protected string $permission = 'manage-settings';
    protected array $search = ['key', 'group'];
    protected array $fields = [
        'key' => ['label' => 'Key', 'rules' => ['required', 'string', 'max:255'], 'unique' => true],
        'value' => ['label' => 'Value', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'type' => ['label' => 'Type', 'type' => 'select', 'options' => ['string' => 'String', 'number' => 'Number', 'boolean' => 'Boolean', 'json' => 'JSON'], 'rules' => ['required', 'in:string,number,boolean,json']],
        'group' => ['label' => 'Group', 'rules' => ['required', 'string', 'max:255']],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
    ];
}
