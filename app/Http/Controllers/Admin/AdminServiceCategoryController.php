<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\ServiceCategory;

class AdminServiceCategoryController extends BaseAdminResourceController
{
    protected string $modelClass = ServiceCategory::class;
    protected string $routePrefix = 'admin.service-categories';
    protected string $viewTitle = 'Service Categories';
    protected string $permission = 'manage-service-categories';
    protected array $fields = [
        'name' => ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['nullable', 'string', 'max:255'], 'unique' => true],
        'short_description' => ['label' => 'Short Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'featured_image' => ['label' => 'Featured Image', 'type' => 'file', 'path' => 'service-categories', 'rules' => ['nullable', 'image', 'max:2048']],
        'seo_title' => ['label' => 'SEO Title', 'rules' => ['nullable', 'string', 'max:255']],
        'seo_description' => ['label' => 'SEO Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive'], 'rules' => ['required', 'in:active,inactive']],
    ];
}
