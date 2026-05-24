<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'settings' => Setting::allSettings(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $current = Setting::allSettings();

        Setting::setValue('site_name', $data['site_name']);
        Setting::setValue('theme_color', $data['theme_color']);

        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('settings', 'public');
            $this->deleteStoredFile($current['site_logo'] ?? null);
            Setting::setValue('site_logo', $path);
        }

        if ($request->hasFile('site_favicon')) {
            $path = $request->file('site_favicon')->store('settings', 'public');
            $this->deleteStoredFile($current['site_favicon'] ?? null);
            Setting::setValue('site_favicon', $path);
        }

        return back()->with('status', 'Settings updated successfully.');
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
