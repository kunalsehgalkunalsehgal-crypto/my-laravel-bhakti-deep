<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Audio;
use App\Models\Admin\Deity;

class AdminDeityController extends BaseAdminResourceController
{
    protected string $modelClass = Deity::class;
    protected string $routePrefix = 'admin.deities';
    protected string $viewTitle = 'Deities';
    protected string $permission = 'manage-deities';
    protected array $relations = ['ambientAudio'];
    protected array $fields = [
        'name' => ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['nullable', 'string', 'max:255'], 'unique' => true],
        'short_description' => ['label' => 'Short Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'featured_image' => ['label' => 'Featured Image', 'type' => 'file', 'path' => 'deities', 'rules' => ['nullable', 'image', 'max:2048']],
        'temple_background_image' => ['label' => 'Background Image', 'type' => 'file', 'path' => 'deities/theme-backgrounds', 'rules' => ['nullable', 'image', 'max:4096']],
        'primary_color' => ['label' => 'Primary Color', 'rules' => ['nullable', 'string', 'max:20']],
        'secondary_color' => ['label' => 'Secondary Color', 'rules' => ['nullable', 'string', 'max:20']],
        'glow_color' => ['label' => 'Glow Color', 'rules' => ['nullable', 'string', 'max:20']],
        'ambient_audio_id' => ['label' => 'Mantra/Music / Ambient Sound', 'type' => 'select', 'options_callback' => 'audioOptions', 'rules' => ['nullable', 'exists:audio_library,id']],
        'particle_style' => ['label' => 'Particle Style', 'type' => 'select', 'options' => ['none' => 'None', 'golden_sparkles' => 'Golden Sparkles', 'flower_petals' => 'Flower Petals', 'smoke' => 'Smoke', 'snow' => 'Snow', 'divine_light' => 'Divine Light'], 'rules' => ['nullable', 'in:none,golden_sparkles,flower_petals,smoke,snow,divine_light']],
        'flame_style' => ['label' => 'Flame Style', 'type' => 'select', 'options' => ['normal' => 'Normal', 'golden' => 'Golden', 'orange' => 'Orange', 'blue' => 'Blue', 'soft' => 'Soft', 'intense' => 'Intense'], 'rules' => ['nullable', 'in:normal,golden,orange,blue,soft,intense']],
        'seo_title' => ['label' => 'SEO Title', 'rules' => ['nullable', 'string', 'max:255']],
        'seo_description' => ['label' => 'SEO Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive'], 'rules' => ['required', 'in:active,inactive']],
    ];

    protected function viewData(array $data = []): array
    {
        return parent::viewData($data + [
            'audioOptions' => Audio::active()->orderBy('title')->pluck('title', 'id')->all(),
        ]);
    }
}
