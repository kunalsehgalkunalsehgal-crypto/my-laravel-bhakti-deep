<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Deity;
use App\Models\Admin\Playlist;
use App\Models\Admin\Service;

class AdminPlaylistController extends BaseAdminResourceController
{
    protected string $modelClass = Playlist::class;
    protected string $routePrefix = 'admin.playlists';
    protected string $viewTitle = 'Playlists';
    protected string $permission = 'manage-playlists';
    protected array $relations = ['service', 'deity'];
    protected array $fields = [
        'service_id' => ['label' => 'Service', 'type' => 'select', 'options_callback' => 'services', 'rules' => ['nullable', 'exists:services,id']],
        'deity_id' => ['label' => 'Deity', 'type' => 'select', 'options_callback' => 'deities', 'rules' => ['nullable', 'exists:deities,id']],
        'name' => ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['nullable', 'string', 'max:255'], 'unique' => true],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive'], 'rules' => ['required', 'in:active,inactive']],
    ];

    protected function viewData(array $data = []): array
    {
        return parent::viewData($data + [
            'services' => Service::orderBy('name')->pluck('name', 'id')->all(),
            'deities' => Deity::orderBy('name')->pluck('name', 'id')->all(),
        ]);
    }
}
