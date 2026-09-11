<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Hawan;
use App\Support\WeeklyBookingAvailability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AdminHawanController extends BaseAdminResourceController
{
    protected string $modelClass = Hawan::class;
    protected string $routePrefix = 'admin.hawans';
    protected string $viewTitle = 'Hawans';
    protected string $permission = 'manage-services';
    protected array $search = ['name', 'short_description'];
    protected array $fields = [
        'name' => ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']],
        'slug' => ['label' => 'Slug', 'rules' => ['nullable', 'string', 'max:255'], 'unique' => true],
        'short_description' => ['label' => 'Short Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'full_description' => ['label' => 'Full Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'featured_image' => ['label' => 'Featured Image', 'type' => 'file', 'path' => 'hawans', 'rules' => ['nullable', 'image', 'max:2048']],
        'base_price' => ['label' => 'Base Price', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
        'samuhik_hawan_enabled' => ['label' => 'Enable Samuhik Hawan', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
        'samuhik_hawan_title' => ['label' => 'Samuhik Title', 'rules' => ['nullable', 'required_if:samuhik_hawan_enabled,1', 'string', 'max:255']],
        'samuhik_hawan_description' => ['label' => 'Samuhik Description', 'type' => 'textarea', 'rules' => ['nullable', 'required_if:samuhik_hawan_enabled,1', 'string']],
        'samuhik_hawan_price' => ['label' => 'Samuhik Price', 'type' => 'number', 'rules' => ['nullable', 'required_if:samuhik_hawan_enabled,1', 'numeric', 'min:0']],
        'special_hawan_enabled' => ['label' => 'Enable Special Hawan', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
        'special_hawan_title' => ['label' => 'Special Title', 'rules' => ['nullable', 'required_if:special_hawan_enabled,1', 'string', 'max:255']],
        'special_hawan_description' => ['label' => 'Special Description', 'type' => 'textarea', 'rules' => ['nullable', 'required_if:special_hawan_enabled,1', 'string']],
        'special_hawan_price' => ['label' => 'Special Price', 'type' => 'number', 'rules' => ['nullable', 'required_if:special_hawan_enabled,1', 'numeric', 'min:0']],
        'duration' => ['label' => 'Duration', 'rules' => ['nullable', 'string', 'max:255']],
        'mode' => ['label' => 'Mode', 'rules' => ['required', 'string', 'max:255']],
        'benefits' => ['label' => 'Benefits', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'included_items' => ['label' => 'Included Items', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'session_timeline' => ['label' => 'Session Timeline', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        'donation_options' => ['label' => 'Donation Options', 'rules' => ['nullable', 'string']],
        'available_slots' => ['label' => 'Available Slots', 'rules' => ['nullable', 'array']],
        'is_featured' => ['label' => 'Featured Hawan', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive'], 'rules' => ['required', 'in:active,inactive']],
    ];

    public function index(Request $request)
    {
        $query = Hawan::query();

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

        return view('admin.hawans.index', compact('records'));
    }

    public function create()
    {
        return view('admin.hawans.form', $this->viewData(['record' => new Hawan(), 'mode' => 'create']));
    }

    public function edit(string $id)
    {
        return view('admin.hawans.form', $this->viewData(['record' => Hawan::findOrFail($id), 'mode' => 'edit']));
    }

    protected function prepareData(Request $request, array $data, ?Model $record = null): array
    {
        $data = parent::prepareData($request, $data, $record);

        foreach (['samuhik_hawan_price', 'special_hawan_price'] as $field) {
            $data[$field] = (float) ($data[$field] ?? 0);
        }

        foreach (['benefits', 'included_items'] as $field) {
            $data[$field] = $this->lines($data[$field] ?? '');
        }

        $data['available_slots'] = WeeklyBookingAvailability::normalize($request->input('available_slots', []), true);

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
