# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Start full dev environment (PHP server + queue worker + Vite HMR concurrently)
composer run dev

# Build frontend assets for production
npm run build

# Run all tests
composer test            # clears config cache first, then runs Pest
php artisan test         # or directly

# Run a single test file
./vendor/bin/pest tests/Feature/ProfileTest.php

# Lint with Laravel Pint
./vendor/bin/pint

# Database
php artisan migrate
php artisan migrate:fresh   # wipe + re-run all migrations

# Required after fresh clone or storage changes
php artisan storage:link

# Clear all caches (needed after config/view changes)
php artisan view:clear && php artisan config:clear && php artisan cache:clear

# First-time setup from scratch
composer run setup
```

## Stack

- **Laravel 13.8 / PHP 8.3** — SQLite database (`database/database.sqlite`)
- **Auth** — Laravel Breeze, session-based (Blade only, no SPA/API)
- **Frontend** — Tailwind CSS v4 via `@tailwindcss/vite` (no `tailwind.config.js`), Alpine.js, Vite 5.4
- **Testing** — Pest v4

## Architecture

### Database schema

```
users           id, name, email, password, profile_picture (nullable), remember_token
projects        id, user_id→users, name, description, color (#hex), status (active/on-hold), picture (nullable)
tasks           id, project_id→projects, title, description, status (todo/in_progress/done),
                priority (low/medium/high), order (int), due_date (nullable date)
task_checklists id, task_id→tasks, title, completed (bool)
```

### Models

- **`User`** — uses PHP 8.3 `#[Fillable([...])]` attribute instead of a `$fillable` property; has `getInitialsAttribute()`
- **`Project`** — has a computed `progress` accessor (% of tasks with `status = done`)
- **`Task`** — `due_date` cast as `date`; `checklists` hasMany relation
- **`TaskChecklist`** — `completed` cast as `bool`

### Controllers

All controllers return redirects (for HTML form submissions) **except**:
- `TaskController::update` — returns JSON (`response()->json($task)`) consumed by drag-and-drop JS
- All `TaskChecklistController` methods — return JSON consumed by the edit-modal JS

Authorization is done with `abort_if($resource->user_id !== $request->user()->id, 403)` inline; no Policy classes exist.

**Non-standard route**: project update is `POST /projects/{project}/update` (not `PUT`) to avoid method-spoofing issues with `multipart/form-data` uploads.

### Frontend patterns

**CSS**: Tailwind v4 is configured entirely inside `resources/css/app.css` using `@theme {}` blocks with OKLch color tokens. Never add a `tailwind.config.js`. Dark mode is class-based: `@variant dark (&:is(.dark *))` — toggled by adding/removing `.dark` on `<html>`.

**JS in Blade views**: Alpine.js is available globally. Page-specific JS lives in `@push('scripts')` blocks at the bottom of each view.

**Passing server data to JS safely**:
- Embed collections as `<script type="application/json" id="some-id">` with `{!! json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP) !!}` — the flags prevent `</script>` injection
- For onclick handlers that need model data, use `data-*` attributes on the element and read `element.dataset.*` in JS — never interpolate user strings directly into JS function call arguments

**Kanban board** (`projects/show.blade.php`): drag-and-drop uses HTML5 native events; on drop it fires `PUT /tasks/{id}` with `{status}` and calls `location.reload()`. The edit task modal keeps checklist state in a `currentChecklists` JS array that is synced back to the `<script type="application/json">` blob after each checklist mutation — **no page reload** for checklist add/toggle/delete, so unsaved title/description edits are preserved.

**File uploads**: stored via `Storage::disk('public')` — avatars under `avatars/`, project images under `projects/`. Always check the return value: `$path = $file->store(...); abort_if($path === false, 500, '...');`. Access in Blade with `asset('storage/' . $path)`.
