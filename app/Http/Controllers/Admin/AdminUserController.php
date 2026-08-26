<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Admin;
use App\Models\Admin\AdminActivityLog;
use App\Models\Admin\AdminRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->routeIs('admin.users.*')) {
            $records = User::when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->search.'%')->orWhere('email', 'like', '%'.$request->search.'%'))
                ->latest()
                ->paginate(15)
                ->withQueryString();

            return view('admin.users.index', compact('records'));
        }

        $records = Admin::with('role')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->search.'%')->orWhere('email', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.admins.index', compact('records'));
    }

    public function create()
    {
        return view('admin.admins.form', ['record' => new Admin(), 'roles' => AdminRole::where('status', 'active')->pluck('name', 'id')]);
    }

    public function store(Request $request)
    {
        $admin = Admin::create($this->validated($request));
        $this->log('create', $admin);

        return redirect()->route('admin.admins.index')->with('success', 'Admin user created successfully.');
    }

    public function edit(Admin $admin)
    {
        return view('admin.admins.form', ['record' => $admin, 'roles' => AdminRole::where('status', 'active')->pluck('name', 'id')]);
    }

    public function update(Request $request, Admin $admin)
    {
        $data = $this->validated($request, $admin);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $admin->update($data);
        $this->log('update', $admin);

        return redirect()->route('admin.admins.index')->with('success', 'Admin user updated successfully.');
    }

    public function destroy(Admin $admin)
    {
        abort_if($admin->id === Auth::guard('admin')->id(), 422, 'You cannot delete your own admin account.');
        $admin->delete();
        $this->log('delete', $admin);

        return redirect()->route('admin.admins.index')->with('success', 'Admin user deleted successfully.');
    }

    private function validated(Request $request, ?Admin $admin = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins')->ignore($admin?->id)],
            'mobile' => ['nullable', 'string', 'max:30'],
            'password' => [$admin ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'exists:admin_roles,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    private function log(string $action, Admin $admin): void
    {
        AdminActivityLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'module' => 'Admin Users',
            'description' => 'Admin '.$admin->email.' '.$action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
