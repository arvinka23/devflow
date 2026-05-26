<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        auth()->logout();
        $user->delete();
        return redirect('/');
    }
}
