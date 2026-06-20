<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardLayoutController extends Controller
{
    private const VALID_WIDGETS = ['stats', 'recent_projects', 'activity_feed', 'kanban_preview', 'time_tracking', 'overdue_tasks'];

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'layout' => 'required|array|min:1|max:10',
            'layout.*' => 'required|string|in:'.implode(',', self::VALID_WIDGETS),
        ]);

        $request->user()->update(['dashboard_layout' => array_values(array_unique($validated['layout']))]);

        return response()->json(['ok' => true]);
    }
}
