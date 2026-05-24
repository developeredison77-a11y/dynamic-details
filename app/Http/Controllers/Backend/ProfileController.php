<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile-images', 'public');

            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $user->profile_image = $path;
        }

        $user->save();

        return back()->with('status', 'Profile updated successfully.');
    }
}
