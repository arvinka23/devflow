<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $request->user()->update($validated);

        return redirect()->route('settings')->with('success', 'Settings saved.');
    }

    public function updateAvatar(Request $request)
    {
        Log::info('updateAvatar called', [
            'hasFile'    => $request->hasFile('avatar'),
            'allFiles'   => array_keys($request->allFiles()),
            'allInput'   => array_keys($request->all()),
        ]);

        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        Log::info('Avatar stored', ['path' => $path]);

        $user->profile_picture = $path;
        $user->save();

        Log::info('User saved', ['profile_picture' => $user->profile_picture]);

        return redirect()->route('settings')->with('success', 'Profile picture updated.');
    }

    public function deleteAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
            $user->update(['profile_picture' => null]);
        }

        return redirect()->route('settings')->with('success', 'Profile picture removed.');
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        auth()->logout();
        $user->delete();
        return redirect('/');
    }
}
