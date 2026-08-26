<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Audio;
use App\Models\Admin\Deity;

class AdminAudioController extends BaseAdminResourceController
{
    protected string $modelClass = Audio::class;
    protected string $routePrefix = 'admin.audio';
    protected string $viewTitle = 'Audio Library';
    protected string $permission = 'manage-audio';
    protected array $relations = ['deity'];
    protected array $search = ['title', 'category'];
    protected array $fields = [
        'deity_id' => ['label' => 'Deity', 'type' => 'select', 'options_callback' => 'deities', 'rules' => ['nullable', 'exists:deities,id']],
        'title' => ['label' => 'Title', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['nullable', 'string', 'max:255'], 'unique' => true],
        'category' => ['label' => 'Category', 'type' => 'select', 'options' => ['mantra' => 'Mantra', 'aarti' => 'Aarti', 'temple_ambience' => 'Temple Ambience', 'hawan_ambience' => 'Hawan Ambience'], 'rules' => ['required', 'string', 'max:255']],
        'audio_file' => ['label' => 'Audio File', 'type' => 'file', 'path' => 'audio', 'rules' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg', 'max:20480']],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive'], 'rules' => ['required', 'in:active,inactive']],
    ];

    protected function viewData(array $data = []): array
    {
        return parent::viewData($data + [
            'deities' => Deity::orderBy('name')->pluck('name', 'id')->all(),
        ]);
    }
}
