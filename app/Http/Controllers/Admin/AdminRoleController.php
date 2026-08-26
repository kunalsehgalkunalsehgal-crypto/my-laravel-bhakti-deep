<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminActivityLog;
use App\Models\Admin\AdminPermission;
use App\Models\Admin\AdminRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminRoleController extends Controller
{
    public function index(Request $request)
    {
        $records = AdminRole::withCount('permissions')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.roles.index', compact('records'));
    }

    public function create()
    {
        return view('admin.roles.form', ['record' => new AdminRole(), 'permissions' => $this->permissions(), 'selected' => []]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $role = AdminRole::create($data);
        $role->permissions()->sync($request->input('permissions', []));
        $this->log('create', $role);

        return redirect()->route('admin.roles.index')->with('success', 'Admin role created successfully.');
    }

    public function edit(AdminRole $role)
    {
        return view('admin.roles.form', [
            'record' => $role,
            'permissions' => $this->permissions(),
            'selected' => $role->permissions()->pluck('admin_permissions.id')->all(),
        ]);
    }

    public function update(Request $request, AdminRole $role)
    {
        $data = $this->validated($request, $role);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $role->update($data);
        $role->permissions()->sync($request->input('permissions', []));
        $this->log('update', $role);

        return redirect()->route('admin.roles.index')->with('success', 'Admin role updated successfully.');
    }

    public function destroy(AdminRole $role)
    {
        abort_if($role->admins()->exists(), 422, 'Role is assigned to admins.');
        $role->delete();
        $this->log('delete', $role);

        return redirect()->route('admin.roles.index')->with('success', 'Admin role deleted successfully.');
    }

    private function validated(Request $request, ?AdminRole $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('admin_roles')->ignore($role?->id)],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:admin_permissions,id'],
        ]);
    }

    private function permissions()
    {
        return AdminPermission::orderBy('module')->orderBy('name')->get()->groupBy('module');
    }

    private function log(string $action, AdminRole $role): void
    {
        AdminActivityLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'module' => 'Admin Roles',
            'description' => 'Admin role '.$role->name.' '.$action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
