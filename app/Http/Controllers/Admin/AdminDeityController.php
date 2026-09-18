<?php 
namespace App\Http\Controllers\Admin;

//  namespace App\Http\Controllers\Admin;

// use App\Models\Admin\Audio;
// use App\Models\Admin\Deity;

// class AdminDeityController extends BaseAdminResourceController
// {
//     protected string $modelClass = Deity::class;

//     protected string $routePrefix = 'admin.deities';

//     protected string $viewTitle = 'Deities';

//     protected string $permission = 'manage-deities';

//     protected array $relations = ['mantraAudio', 'ambientAudio'];

//     protected array $fields = [
//         'name' => ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']],
//         'slug' => ['label' => 'Slug', 'rules' => ['nullable', 'string', 'max:255'], 'unique' => true],
//         'short_description' => ['label' => 'Short Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
//         'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
//         'featured_image' => ['label' => 'Featured Image', 'type' => 'file', 'path' => 'deities', 'rules' => ['nullable', 'image', 'max:2048']],
//         'temple_background_image' => ['label' => 'Background Image', 'type' => 'file', 'path' => 'deities/theme-backgrounds', 'rules' => ['nullable', 'image', 'max:4096']],
//         'primary_color' => ['label' => 'Primary Color', 'rules' => ['nullable', 'string', 'max:20']],
//         'secondary_color' => ['label' => 'Secondary Color', 'rules' => ['nullable', 'string', 'max:20']],
//         'glow_color' => ['label' => 'Glow Color', 'rules' => ['nullable', 'string', 'max:20']],
//         'mantra_audio_id' => ['label' => 'Mantra/Music', 'type' => 'select', 'options_callback' => 'mantraAudioOptions', 'rules' => ['nullable', 'exists:audio_library,id']],
//         'ambient_audio_id' => ['label' => 'Ambient Sound', 'type' => 'select', 'options_callback' => 'ambientAudioOptions', 'rules' => ['nullable', 'exists:audio_library,id']],
//         'particle_style' => ['label' => 'Particle Style', 'type' => 'select', 'options' => ['none' => 'None', 'golden_sparkles' => 'Golden Sparkles', 'flower_petals' => 'Flower Petals', 'smoke' => 'Smoke', 'snow' => 'Snow', 'divine_light' => 'Divine Light'], 'rules' => ['nullable', 'in:none,golden_sparkles,flower_petals,smoke,snow,divine_light']],
//         'flame_style' => ['label' => 'Flame Style', 'type' => 'select', 'options' => ['normal' => 'Normal', 'golden' => 'Golden', 'orange' => 'Orange', 'blue' => 'Blue', 'soft' => 'Soft', 'intense' => 'Intense'], 'rules' => ['nullable', 'in:normal,golden,orange,blue,soft,intense']],
//         'seo_title' => ['label' => 'SEO Title', 'rules' => ['nullable', 'string', 'max:255']],
//         'seo_description' => ['label' => 'SEO Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
//         'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive'], 'rules' => ['required', 'in:active,inactive']],
//     ];

//     protected function viewData(array $data = []): array
//     {
//         return parent::viewData($data + [
//             'mantraAudioOptions' => Audio::active()->whereIn('category', ['mantra', 'aarti'])->orderBy('title')->pluck('title', 'id')->all(),
//             'ambientAudioOptions' => Audio::active()->whereIn('category', ['temple_ambience', 'hawan_ambience'])->orderBy('title')->pluck('title', 'id')->all(),
//         ]);
//     }
// } 
// <?php

// namespace App\Http\Controllers\Admin;

use App\Models\Admin\Audio;
use App\Models\Admin\Deity;

class AdminDeityController extends BaseAdminResourceController
{
    protected string $modelClass = Deity::class;

    protected string $routePrefix = 'admin.deities';

    protected string $viewTitle = 'Deities';

    protected string $permission = 'manage-deities';

    protected array $relations = [
        'mantraAudio',
        'aartiAudio',
        'ambientAudio',
    ];

    protected array $fields = [

        'name' => [
            'label' => 'Name',
            'rules' => [
                'required',
                'string',
                'max:255',
            ],
        ],

        'slug' => [
            'label' => 'Slug',
            'rules' => [
                'nullable',
                'string',
                'max:255',
            ],
            'unique' => true,
        ],

        'short_description' => [
            'label' => 'Short Description',
            'type' => 'textarea',
            'rules' => [
                'nullable',
                'string',
            ],
        ],

        'description' => [
            'label' => 'Description',
            'type' => 'textarea',
            'rules' => [
                'nullable',
                'string',
            ],
        ],

        'featured_image' => [
            'label' => 'Featured Image',
            'type' => 'file',
            'path' => 'deities',
            'rules' => [
                'nullable',
                'image',
                'max:2048',
            ],
        ],

        'temple_background_image' => [
            'label' => 'Background Image',
            'type' => 'file',
            'path' => 'deities/theme-backgrounds',
            'rules' => [
                'nullable',
                'image',
                'max:4096',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | AARTI VIDEO
        |--------------------------------------------------------------------------
        */

        'aarti_video' => [
            'label' => 'Aarti Video',
            'type' => 'file',
            'path' => 'deities/aarti-videos',
            'rules' => [
                'nullable',
                'file',
                'mimes:mp4,webm,mov',
                'max:51200',
            ],
        ],

        'primary_color' => [
            'label' => 'Primary Color',
            'rules' => [
                'nullable',
                'string',
                'max:20',
            ],
        ],

        'secondary_color' => [
            'label' => 'Secondary Color',
            'rules' => [
                'nullable',
                'string',
                'max:20',
            ],
        ],

        'glow_color' => [
            'label' => 'Glow Color',
            'rules' => [
                'nullable',
                'string',
                'max:20',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | MANTRA AUDIO
        |--------------------------------------------------------------------------
        */

        'mantra_audio_id' => [
            'label' => 'Mantra Audio',
            'type' => 'select',
            'options_callback' => 'mantraAudioOptions',
            'rules' => [
                'nullable',
                'exists:audio_library,id',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | AARTI AUDIO
        |--------------------------------------------------------------------------
        */

        'aarti_audio_id' => [
            'label' => 'Aarti Audio',
            'type' => 'select',
            'options_callback' => 'aartiAudioOptions',
            'rules' => [
                'nullable',
                'exists:audio_library,id',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | AMBIENT AUDIO
        |--------------------------------------------------------------------------
        */

        'ambient_audio_id' => [
            'label' => 'Ambient Sound',
            'type' => 'select',
            'options_callback' => 'ambientAudioOptions',
            'rules' => [
                'nullable',
                'exists:audio_library,id',
            ],
        ],

        'particle_style' => [
            'label' => 'Particle Style',
            'type' => 'select',
            'options' => [
                'none' => 'None',
                'golden_sparkles' => 'Golden Sparkles',
                'flower_petals' => 'Flower Petals',
                'smoke' => 'Smoke',
                'snow' => 'Snow',
                'divine_light' => 'Divine Light',
            ],
            'rules' => [
                'nullable',
                'in:none,golden_sparkles,flower_petals,smoke,snow,divine_light',
            ],
        ],

        'flame_style' => [
            'label' => 'Flame Style',
            'type' => 'select',
            'options' => [
                'normal' => 'Normal',
                'golden' => 'Golden',
                'orange' => 'Orange',
                'blue' => 'Blue',
                'soft' => 'Soft',
                'intense' => 'Intense',
            ],
            'rules' => [
                'nullable',
                'in:normal,golden,orange,blue,soft,intense',
            ],
        ],

        'seo_title' => [
            'label' => 'SEO Title',
            'rules' => [
                'nullable',
                'string',
                'max:255',
            ],
        ],

        'seo_description' => [
            'label' => 'SEO Description',
            'type' => 'textarea',
            'rules' => [
                'nullable',
                'string',
            ],
        ],

        'status' => [
            'label' => 'Status',
            'type' => 'select',
            'options' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
            'rules' => [
                'required',
                'in:active,inactive',
            ],
        ],
    ];


    protected function viewData(array $data = []): array
    {
        return parent::viewData($data + [

            'mantraAudioOptions' => Audio::active()
                ->where('category', 'mantra')
                ->orderBy('title')
                ->pluck('title', 'id')
                ->all(),

            'aartiAudioOptions' => Audio::active()
                ->where('category', 'aarti')
                ->orderBy('title')
                ->pluck('title', 'id')
                ->all(),

            'ambientAudioOptions' => Audio::active()
                ->whereIn('category', [
                    'temple_ambience',
                    'hawan_ambience',
                ])
                ->orderBy('title')
                ->pluck('title', 'id')
                ->all(),

        ]);
    }
}