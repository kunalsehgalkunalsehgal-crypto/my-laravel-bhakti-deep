<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminDiyaRequest;
use App\Models\Admin\AdminActivityLog;
use App\Models\Admin\Audio;
use App\Models\Admin\Deity;
use App\Models\Admin\Diya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminDiyaController extends Controller
{
    public function index(Request $request)
    {
        $records = Diya::with(['fixedDeity', 'mantraAudio'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($builder) use ($request) {
                    $builder->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('slug', 'like', '%'.$request->search.'%')
                        ->orWhere('category', 'like', '%'.$request->search.'%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('deity_selection_mode'), fn ($query) => $query->where('deity_selection_mode', $request->deity_selection_mode))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.diyas.index', compact('records'));
    }

    public function create()
    {
        return view('admin.diyas.form', [
            'record' => new Diya(['deity_selection_mode' => Diya::MODE_USER_SELECT, 'status' => 'active']),
            'mode' => 'create',
            'activeDeities' => $this->activeDeities(),
        ]);
    }

    public function store(AdminDiyaRequest $request)
    {
        $diya = Diya::create($this->data($request));
        $this->log('create', $diya);

        return redirect()->route('admin.diyas.index')->with('success', 'Diya created successfully.');
    }

    public function show(Diya $diya)
    {
        $diya->load(['fixedDeity', 'mantraAudio.deity']);

        return view('admin.diyas.show', compact('diya'));
    }

    public function edit(Diya $diya)
    {
        $diya->load('mantraAudio');

        return view('admin.diyas.form', [
            'record' => $diya,
            'mode' => 'edit',
            'activeDeities' => $this->activeDeities(),
        ]);
    }

    public function mantraAudios(Request $request)
    {
        $request->validate([
            'deity_id' => [
                'required',
                'integer',
                'exists:deities,id',
            ],
        ]);

        $audios = Audio::active()
            ->where('category', 'mantra')
            ->where('deity_id', $request->integer('deity_id'))
            ->whereNotNull('audio_file')
            ->orderBy('title')
            ->get(['id', 'title']);

        return response()->json($audios);
    }

    public function update(AdminDiyaRequest $request, Diya $diya)
    {
        $diya->update($this->data($request, $diya));
        $this->log('update', $diya);

        return redirect()->route('admin.diyas.index')->with('success', 'Diya updated successfully.');
    }

    public function destroy(Diya $diya)
    {
        $this->log('delete', $diya);
        $diya->delete();

        return redirect()->route('admin.diyas.index')->with('success', 'Diya deleted successfully.');
    }

    public function toggleStatus(Diya $diya)
    {
        $diya->update(['status' => $diya->status === 'active' ? 'inactive' : 'active']);
        $this->log('status_update', $diya);

        return back()->with('success', 'Diya status updated successfully.');
    }

    private function data(AdminDiyaRequest $request, ?Diya $diya = null): array
    {
        $data = $request->validated();
        unset($data['mantra_deity_id']);

        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        if ($request->hasFile('image')) {
            if ($diya?->image && Storage::disk('public')->exists($diya->image)) {
                Storage::disk('public')->delete($diya->image);
            }

            $data['image'] = $request->file('image')->store('diyas', 'public');
        } elseif ($diya) {
            unset($data['image']);
        }

        if ($data['deity_selection_mode'] === Diya::MODE_USER_SELECT) {
            $data['fixed_deity_id'] = null;
        }

        return $data;
    }

    private function activeDeities()
    {
        return Deity::where('status', 'active')->orderBy('name')->get();
    }

    private function log(string $action, Diya $diya): void
    {
        AdminActivityLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'module' => 'Diyas',
            'description' => 'Diyas #'.$diya->id.' '.$action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
