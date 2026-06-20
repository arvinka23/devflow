<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        return to_route('settings')->with('success', 'Settings saved.');
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

        return to_route('settings')->with('success', 'Profile picture updated.');
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

        return to_route('settings')->with('success', 'Profile picture removed.');
    }

    public function updateGithubToken(Request $request)
    {
        $request->validate([
            'github_token' => 'nullable|string|max:500',
        ]);

        $token = $request->input('github_token');

        // Reject if the user accidentally submitted placeholder bullet characters
        if ($token && preg_match('/^[•\*]+$/', $token)) {
            return to_route('settings')->withErrors(['github_token' => 'Please paste your actual GitHub token, not the placeholder.']);
        }

        $request->user()->update(['github_token' => $token ?: null]);

        // Flush the cached repo list so the picker immediately reflects the new token.
        Cache::forget('github.repos.' . $request->user()->id);

        return to_route('settings')->with('success', 'GitHub token saved.');
    }

    public function updateNotifications(Request $request)
    {
        // Unchecked checkboxes are not submitted — default each to false
        $prefs = [
            'task_due_reminders' => (bool) $request->input('task_due_reminders', false),
            'project_updates'    => (bool) $request->input('project_updates', false),
            'weekly_summary'     => (bool) $request->input('weekly_summary', false),
        ];

        $request->user()->update(['notification_preferences' => $prefs]);
        return to_route('settings')->with('success', 'Notification preferences saved.');
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $user->delete();
        return redirect('/');
    }
}
