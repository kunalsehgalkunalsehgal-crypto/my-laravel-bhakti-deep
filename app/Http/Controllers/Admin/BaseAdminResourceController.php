<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;





abstract class BaseAdminResourceController extends Controller
{
    protected string $modelClass;
    protected string $routePrefix;
    protected string $viewTitle;
    protected string $permission;
    protected array $fields = [];
    protected array $relations = [];
    protected array $search = ['name', 'title', 'email'];

    public function index(Request $request)
    {
        $query = $this->modelClass::query();

        foreach ($this->relations as $relation) {
            $query->with($relation);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $columns = array_filter($this->search, fn ($column) => $this->hasField($column));

            $query->where(function ($builder) use ($columns, $search) {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        if ($request->filled('status') && $this->hasField('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->latest()->paginate(15)->withQueryString();

        return view('admin.crud.index', $this->viewData(compact('records')));
    }

    public function create()
    {
        return view('admin.crud.form', $this->viewData(['record' => new $this->modelClass(), 'mode' => 'create']));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $record = $this->modelClass::create($this->prepareData($request, $data));
        $this->afterSave($record, $request);
        $this->log('create', $record);

        return redirect()->route($this->routePrefix.'.index')->with('success', $this->viewTitle.' created successfully.');
    }

    public function show(string $id)
    {
        $record = $this->findRecord($id);

        return view('admin.crud.show', $this->viewData(compact('record')));
    }

    public function edit(string $id)
    {
        $record = $this->findRecord($id);

        return view('admin.crud.form', $this->viewData(compact('record') + ['mode' => 'edit']));
    }

    public function update(Request $request, string $id)
    {
        $record = $this->findRecord($id);
        $data = $this->validated($request, $record);
        $record->update($this->prepareData($request, $data, $record));
        $this->afterSave($record, $request);
        $this->log('update', $record);

        return redirect()->route($this->routePrefix.'.index')->with('success', $this->viewTitle.' updated successfully.');
    }

    // public function destroy(string $id)
    // {
    //     $record = $this->findRecord($id);
    //     $record->delete();
    //     $this->log('delete', $record);

    //     return redirect()->route($this->routePrefix.'.index')->with('success', $this->viewTitle.' deleted successfully.');
    // }
public function destroy(string $id)
{
    // Blog record find karo
    $record = $this->findRecord($id);

    // Dono image paths database se lo
    $imagePaths = [
        $record->image,
        $record->og_image,
    ];

    // Pehle activity log save karo
    $this->log('delete', $record);

    // Database se record delete karo
    $record->forceDelete();

    // Public storage se dono images delete karo
    foreach ($imagePaths as $imagePath) {
        if (
            !empty($imagePath) &&
            Storage::disk('public')->exists($imagePath)
        ) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    return redirect()
        ->route($this->routePrefix . '.index')
        ->with(
            'success',
            $this->viewTitle . ' deleted successfully.'
        );
}





    protected function validated(Request $request, ?Model $record = null): array
    {
        $rules = [];

        foreach ($this->fields as $name => $field) {
            if (($field['readonly'] ?? false) || ($field['display_only'] ?? false)) {
                continue;
            }

            $fieldRules = $field['rules'] ?? ['nullable'];

            if (($field['unique'] ?? false) === true) {
                $fieldRules[] = Rule::unique($field['table'] ?? $this->table(), $name)->ignore($record?->getKey());
            }

            $rules[$name] = $fieldRules;
        }

        return $request->validate($rules);
    }

    protected function prepareData(Request $request, array $data, ?Model $record = null): array
    {
        foreach ($this->fields as $name => $field) {
            if (($field['type'] ?? null) === 'file') {
                unset($data[$name]);

                if ($request->hasFile($name)) {
                    $data[$name] = $request->file($name)->store($field['path'] ?? 'admin', 'public');
                } elseif ($record) {
                    $data[$name] = $record->{$name};
                }
            }

            if (($field['type'] ?? null) === 'checkbox') {
                $data[$name] = $request->boolean($name);
            }
        }

        if (array_key_exists('slug', $this->fields) && empty($data['slug'])) {
            $source = $data['name'] ?? $data['title'] ?? Str::random(8);
            $data['slug'] = Str::slug($source);
        }

        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
    }

    protected function findRecord(string $id): Model
    {
        $query = $this->modelClass::query();

        foreach ($this->relations as $relation) {
            $query->with($relation);
        }

        return $query->findOrFail($id);
    }

    protected function viewData(array $data = []): array
    {
        return $data + [
            'title' => $this->viewTitle,
            'routePrefix' => $this->routePrefix,
            'fields' => $this->fields,
        ];
    }

    protected function log(string $action, Model $record): void
    {
        AdminActivityLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'module' => $this->viewTitle,
            'description' => $this->viewTitle.' #'.$record->getKey().' '.$action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    protected function hasField(string $field): bool
    {
        return array_key_exists($field, $this->fields);
    }

    protected function table(): string
    {
        return (new $this->modelClass())->getTable();
    }
}
