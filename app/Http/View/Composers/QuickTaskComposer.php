<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;

class QuickTaskComposer
{
    public function compose(View $view): void
    {
        if (!auth()->check()) {
            return;
        }

        $view->with('quickTaskProjects', auth()->user()->projects()->orderBy('name')->get(['id', 'name', 'color']));
    }
}
