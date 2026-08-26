<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Pooja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPoojaController extends BaseAdminResourceController
{
    protected string $modelClass = Pooja::class;
    protected string $routePrefix = 'admin.poojas';
    protected string $viewTitle = 'Poojas';
    protected string $permission = 'manage-services';
    protected array $search = ['name', 'short_description'];
    protected array $fields = [
        'name' => ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['nullable', 'string', 'max:255'], 'unique' => true],
        'short_description' => ['label' => 'Short Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'full_description' => ['label' => 'Full Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'featured_image' => ['label' => 'Featured Image', 'type' => 'file', 'path' => 'poojas', 'rules' => ['nullable', 'image', 'max:2048']],
        'base_price' => ['label' => 'Base Price', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
        'duration' => ['label' => 'Duration', 'rules' => ['nullable', 'string', 'max:255']],
        'mode' => ['label' => 'Mode', 'rules' => ['required', 'string', 'max:255']],
        'benefits' => ['label' => 'Benefits', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'included_items' => ['label' => 'Included Items', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'session_timeline' => ['label' => 'Session Timeline', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'donation_options' => ['label' => 'Donation Options', 'rules' => ['nullable', 'string']],
        'available_slots' => ['label' => 'Available Slots', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'is_featured' => ['label' => 'Featured Pooja', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive'], 'rules' => ['required', 'in:active,inactive']],
    ];

    public function index(Request $request)
    {
        $query = Pooja::query();

        if ($request->filled('search')) {
            $query->where(function ($builder) use ($request) {
                $builder->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('short_description', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->latest()->paginate(15)->withQueryString();

        return view('admin.poojas.index', compact('records'));
    }

    public function create()
    {
        return view('admin.poojas.form', $this->viewData(['record' => new Pooja(), 'mode' => 'create']));
    }

    public function edit(string $id)
    {
        return view('admin.poojas.form', $this->viewData(['record' => Pooja::findOrFail($id), 'mode' => 'edit']));
    }

    protected function prepareData(Request $request, array $data, ?Model $record = null): array
    {
        $data = parent::prepareData($request, $data, $record);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['status'] = $data['status'] ?: 'active';

        foreach (['benefits', 'included_items', 'available_slots'] as $field) {
            $data[$field] = $this->lines($data[$field] ?? '');
        }

        $data['donation_options'] = collect(explode(',', $data['donation_options'] ?? ''))
            ->map(fn ($amount) => (int) trim($amount))
            ->filter()
            ->values()
            ->all();

        $data['session_timeline'] = collect($this->lines($data['session_timeline'] ?? ''))
            ->map(function ($line) {
                [$title, $duration] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
                return ['title' => $title, 'duration' => $duration];
            })
            ->filter(fn ($item) => $item['title'] !== '')
            ->values()
            ->all();

        return $data;
    }

    private function lines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
