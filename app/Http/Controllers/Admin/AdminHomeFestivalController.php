<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminHomeFestivalController extends Controller
{
    public function edit()
    {
        $settings = PlatformSetting::whereIn('key', [
            'home_festival_heading',
            'home_festival_subheading',
            'home_festival_image'
        ])->pluck('value', 'key');

        $lines = preg_split(
            '/\R/u',
            $settings->get('home_festival_heading')
                ?: "Guru Purnima\nMahotsav"
        );

        return view(
            'admin.home-festival.edit',
            compact('settings', 'lines')
        );
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'first_line' => 'required|string|max:36',
            'second_line' => 'required|string|max:36',
            'subheading' => 'required|string|max:200',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $values = [
            'home_festival_heading' =>
                trim($data['first_line']) . "\n" . trim($data['second_line']),

            'home_festival_subheading' =>
                trim($data['subheading']),
        ];

        $oldImage = null;

        if ($request->hasFile('image')) {

            $oldImage = PlatformSetting::where(
                'key',
                'home_festival_image'
            )->value('value');

            $values['home_festival_image'] = $request
                ->file('image')
                ->store('home-festival', 'public');

            if (!$values['home_festival_image']) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'Image upload failed.'
                    ]);
            }
        }

        foreach ($values as $key => $value) {

            PlatformSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => 'string',
                    'group' => 'homepage'
                ]
            );
        }

        if (
            $oldImage &&
            str_starts_with($oldImage, 'home-festival/')
        ) {
            Storage::disk('public')->delete($oldImage);
        }

        return back()->with(
            'success',
            'Festival section updated successfully.'
        );
    }
}