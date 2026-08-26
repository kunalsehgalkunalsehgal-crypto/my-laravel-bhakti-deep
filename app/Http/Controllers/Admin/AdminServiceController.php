<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Deity;
use App\Models\Admin\Service;
use App\Models\Admin\ServiceCategory;

class AdminServiceController extends BaseAdminResourceController
{
    protected string $modelClass = Service::class;
    protected string $routePrefix = 'admin.services';
    protected string $viewTitle = 'Services';
    protected string $permission = 'manage-services';
    protected array $relations = ['category', 'deity'];
    protected array $fields = [
        'service_category_id' => ['label' => 'Category', 'type' => 'select', 'options_callback' => 'categories', 'rules' => ['nullable', 'exists:service_categories,id']],
        'deity_id' => ['label' => 'Deity', 'type' => 'select', 'options_callback' => 'deities', 'rules' => ['nullable', 'exists:deities,id']],
        'name' => ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['nullable', 'string', 'max:255'], 'unique' => true],
        'short_description' => ['label' => 'Short Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'full_description' => ['label' => 'Full Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'featured_image' => ['label' => 'Featured Image', 'type' => 'file', 'path' => 'services', 'rules' => ['nullable', 'image', 'max:2048']],
        'price' => ['label' => 'Price', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0']],
        'donation_type' => ['label' => 'Donation Type', 'type' => 'select', 'options' => ['fixed' => 'Fixed', 'open' => 'Open', 'free' => 'Free'], 'rules' => ['required', 'in:fixed,open,free']],
        'service_theme' => ['label' => 'Theme', 'rules' => ['nullable', 'string', 'max:255']],
        'seo_title' => ['label' => 'SEO Title', 'rules' => ['nullable', 'string', 'max:255']],
        'seo_description' => ['label' => 'SEO Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive'], 'rules' => ['required', 'in:active,inactive']],
    ];

    protected function viewData(array $data = []): array
    {
        return parent::viewData($data + [
            'categories' => ServiceCategory::orderBy('name')->pluck('name', 'id')->all(),
            'deities' => Deity::orderBy('name')->pluck('name', 'id')->all(),
        ]);
    }
}
