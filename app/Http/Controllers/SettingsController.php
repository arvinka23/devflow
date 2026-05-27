<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        // Explicitly pass only the name to prevent accidental mass-assignment
        // of other fillable fields (e.g. email) if validation rules expand later.
        $request->user()->update(['name' => $validated['name']]);

        return redirect()->route('settings')->with('success', 'Settings saved.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar_base64' => 'required|string',
        ]);

        $path = $this->saveBase64Avatar($request->input('avatar_base64'));
        if ($path === null) {
            return back()->withErrors(['avatar' => 'The image could not be processed. Use a JPG, PNG, WebP, or GIF under 4 MB.']);
        }

        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->profile_picture = $path;
        $user->save();

        return redirect()->route('settings')->with('success', 'Profile picture updated.');
    }

    private function saveBase64Avatar(string $base64): ?string
    {
        if (!preg_match('/^data:image\/[a-z]+;base64,(.+)$/s', $base64, $matches)) {
            return null;
        }

        $imageData = base64_decode($matches[1], true);

        if ($imageData === false || strlen($imageData) > 4 * 1024 * 1024) {
            return null;
        }

        // Verify actual image content — never trust the client-supplied MIME prefix.
        $mime       = (new \finfo(FILEINFO_MIME_TYPE))->buffer($imageData);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

        if (!array_key_exists($mime, $extensions)) {
            return null;
        }

        $filename = 'avatars/' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $stored   = Storage::disk('public')->put($filename, $imageData);

        return $stored ? $filename : null;
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

        // Delete first — if this fails, the user stays logged in and can retry.
        $user->delete();
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
