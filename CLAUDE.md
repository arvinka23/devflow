# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

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

---

## Project Overview

**DevFlow** is a session-based project management web app. Core features:

- Kanban board (per-project, drag-drop columns)
- Calendar and list views per project
- Milestones, labels, task checklists
- Time tracking (start/stop timers per task, project report)
- Task comments and activity log
- GitHub repo linking (PRs + branches)
- AI-powered task suggestions and chat (Anthropic API)
- Dashboard with customisable widgets
- User settings, avatar upload, notification preferences

Auth is standard Laravel Breeze (session/Blade). No SPA, no REST API — all HTML form submissions with a few JSON endpoints for interactive UI.

---

## Stack & Dependencies

| Layer | Technology |
|---|---|
| Backend | Laravel 13.8, PHP 8.3 |
| Database | SQLite (`database/database.sqlite`) |
| Auth | Laravel Breeze (session-based) |
| Frontend | Tailwind CSS v4 (via `@tailwindcss/vite`), Alpine.js 3.4.2, Vite 5.4 |
| Testing | Pest v4 |
| AI | Anthropic SDK (`anthropic/sdk`) |
| Queue/Cache/Session | All use the `database` driver |

No `tailwind.config.js` — Tailwind v4 is configured entirely in `resources/css/app.css` via `@theme {}` blocks.

---

## Directory Structure

```
app/
  Http/
    Controllers/          20 controllers
    Requests/             Auth/ProfileUpdateRequest
    View/Composers/       QuickTaskComposer (sidebar projects)
  Models/                 8 Eloquent models
  Services/               AiAssistantService
  Providers/              AppServiceProvider
  View/Components/        AppLayout, GuestLayout

config/                   app, auth, cache, database, filesystems, logging, mail, queue, services, session

database/
  migrations/             19 migrations
  factories/
  seeders/

resources/
  views/                  50+ Blade templates
  css/app.css             Tailwind @theme + dark mode
  js/app.js               5 Alpine.js components

routes/
  web.php                 All feature routes (~90 lines)
  auth.php                Breeze auth routes (~60 lines)
  console.php             inspire command only

tests/
  Feature/
    Auth/                 6 auth tests (Breeze defaults)
    ProfileTest.php
  Unit/
    ExampleTest.php
```

---

## Routes

### Auth (`routes/auth.php` — standard Breeze)

| Method | URI | Action |
|---|---|---|
| GET/POST | /register | RegisteredUserController |
| GET/POST | /login | AuthenticatedSessionController |
| POST | /logout | AuthenticatedSessionController@destroy |
| GET/POST | /forgot-password | PasswordResetLinkController |
| GET/POST | /reset-password/{token} | NewPasswordController |
| GET | /verify-email | EmailVerificationPromptController |
| GET | /verify-email/{id}/{hash} | VerifyEmailController |
| POST | /email/verification-notification | EmailVerificationNotificationController |
| GET/POST | /confirm-password | ConfirmablePasswordController |
| PUT | /password | PasswordController |

### Feature (`routes/web.php` — all under `middleware('auth')`)

```
GET  /dashboard                          DashboardController@index
PUT  /dashboard/layout                   DashboardLayoutController@update
GET  /search                             SearchController@index

GET    /projects                         ProjectController@index
POST   /projects                         ProjectController@store
GET    /projects/{project}               ProjectController@show  (Kanban board)
POST   /projects/{project}/update        ProjectController@update  (POST, not PUT — multipart upload)
DELETE /projects/{project}               ProjectController@destroy
POST   /projects/{project}/toggle-status ProjectController@toggleStatus
POST   /projects/{project}/archive       ProjectController@archive
POST   /projects/{project}/unarchive     ProjectController@unarchive

GET  /projects/{project}/calendar        CalendarController@show
GET  /projects/{project}/list            ListViewController@show
GET  /projects/{project}/export/json     ExportController@json
GET  /projects/{project}/export/csv      ExportController@csv

GET    /projects/{project}/milestones              MilestoneController@index
POST   /projects/{project}/milestones              MilestoneController@store
PUT    /projects/{project}/milestones/{milestone}  MilestoneController@update
DELETE /projects/{project}/milestones/{milestone}  MilestoneController@destroy

POST   /tasks                            TaskController@store
PUT    /tasks/{task}                     TaskController@update  → returns JSON
DELETE /tasks/{task}                     TaskController@destroy
POST   /projects/{project}/tasks/reorder TaskController@reorder

POST   /tasks/{task}/comments               TaskCommentController@store
DELETE /tasks/{task}/comments/{comment}     TaskCommentController@destroy
POST   /tasks/{task}/checklists             TaskChecklistController@store     → JSON
PUT    /tasks/{task}/checklists/{checklist} TaskChecklistController@update    → JSON
DELETE /tasks/{task}/checklists/{checklist} TaskChecklistController@destroy   → JSON
POST   /tasks/{task}/labels                 LabelController@attach
DELETE /tasks/{task}/labels/{label}         LabelController@detach

GET    /labels           LabelController@index
POST   /labels           LabelController@store
DELETE /labels/{label}   LabelController@destroy

GET    /tasks/{task}/time                   TimeEntryController@index
GET    /projects/{project}/time-report      TimeEntryController@projectReport
POST   /tasks/{task}/time/start             TimeEntryController@start
POST   /tasks/{task}/time/stop              TimeEntryController@stop
DELETE /time-entries/{timeEntry}            TimeEntryController@destroy

POST /ai/suggest-tasks   AiController@suggestTasks
POST /ai/chat            AiController@chat

GET  /github/repos                        GitHubController@repos
GET  /projects/{project}/github           GitHubController@show
POST /projects/{project}/github/link      GitHubController@link
POST /projects/{project}/github/unlink    GitHubController@unlink

GET    /settings                               SettingsController@index
PUT    /settings                               SettingsController@update
POST   /settings/avatar                        SettingsController@updateAvatar
DELETE /settings/avatar                        SettingsController@deleteAvatar
POST   /settings/github-token                  SettingsController@updateGithubToken
POST   /settings/notifications                 SettingsController@updateNotifications
DELETE /account                                SettingsController@deleteAccount
```

---

## Models & Relationships

### User
```php
#[Fillable(['name', 'email', 'password', 'profile_picture', 'github_token',
            'dashboard_layout', 'notification_preferences'])]
// Uses PHP 8.3 attribute syntax instead of $fillable property
// casts: email_verified_at→datetime, password→hashed, github_token→encrypted,
//        dashboard_layout→array, notification_preferences→array
// hasMany: Project, Label
// accessors: getInitialsAttribute(), notificationPreference(key, default)
```

### Project
```php
// fillable: user_id, name, description, color, status, picture, github_repo, archived
// belongsTo: User
// hasMany: Task, ActivityLog, Milestone
// accessor: getProgressAttribute() — % of tasks with status=done
```

### Task
```php
// fillable: project_id, milestone_id, title, description, status, priority, order, due_date
// casts: due_date→date
// belongsTo: Project, Milestone (optional)
// hasMany: TaskChecklist, TimeEntry, TaskComment (ordered latest first)
// belongsToMany: Label via label_task pivot
// status: todo | in_progress | done
// priority: low | medium | high
```

### Label
```php
// fillable: user_id, name, color
// DB constraint: unique(user_id, name)
// belongsToMany: Task
```

### TaskChecklist
```php
// fillable: task_id, title, completed
// casts: completed→boolean
```

### TimeEntry
```php
// fillable: task_id, user_id, started_at, ended_at, duration_seconds
// casts: started_at→datetime, ended_at→datetime
// scope: running() — WHERE ended_at IS NULL
// accessor: getDurationHumanAttribute() — "Xh Ym" or "Xm Ys"
```

### ActivityLog
```php
// fillable: user_id, project_id, task_id, event, subject, meta
// casts: meta→array
// static: ActivityLog::log(User, event, subject, ?Project, ?Task, meta)
// DB indexes: user_id, project_id, (user_id, created_at)
```

### Milestone
```php
// fillable: project_id, title, due_date
// casts: due_date→date
// accessor: getProgressAttribute() — N+1 safe, uses pre-loaded tasks_count/done_count
```

---

## Database Schema

**users** — `id, name, email (unique), email_verified_at, password, profile_picture, remember_token, github_token (encrypted), dashboard_layout (JSON), notification_preferences (JSON)`

**projects** — `id, user_id→users CASCADE, name, description, color (default #6366f1), status ENUM(active,on-hold), picture, github_repo, archived (bool)`

**tasks** — `id, project_id→projects CASCADE, title, description, status ENUM(todo,in_progress,done), priority ENUM(low,medium,high), order INT, due_date, milestone_id→milestones NULL ON DELETE`

**task_checklists** — `id, task_id→tasks CASCADE, title, completed BOOL`

**task_comments** — `id, task_id→tasks CASCADE, user_id→users CASCADE, body TEXT`

**milestones** — `id, project_id→projects CASCADE, title, due_date`

**labels** — `id, user_id→users CASCADE, name (max 50), color (max 20), UNIQUE(user_id, name)`

**label_task** (pivot) — `label_id→labels CASCADE, task_id→tasks CASCADE, PRIMARY KEY(label_id, task_id)`

**time_entries** — `id, task_id→tasks CASCADE, user_id→users CASCADE, started_at, ended_at (nullable), duration_seconds (nullable), INDEX(user_id, started_at)`

**activity_logs** — `id, user_id, project_id (nullable), task_id (nullable), event, subject, meta (JSON), INDEXES: user_id, project_id, (user_id, created_at)`

**Infrastructure** — `sessions, jobs, job_batches, cache, cache_locks, password_reset_tokens`

---

## Controllers

### Authorization pattern (no Policy classes)
```php
abort_if($resource->user_id !== $request->user()->id, 403);
```

### Response conventions
- HTML form submissions → `redirect()->back()` or `redirect()->route(...)`
- `TaskController::update()` → JSON (drag-drop)
- All `TaskChecklistController` methods → JSON (edit-modal JS)

### Key controller notes
- **ProjectController** — accepts `picture_base64`; validates MIME with `finfo()`, enforces 2 MB limit
- **TaskController** — `reorder()` does batch update of `order` column; `update()` logs activity on status change
- **TimeEntryController** — `start()` uses `DB::transaction()` + `lockForUpdate()` to prevent duplicate running timers; `stop()` calculates `duration_seconds = now() - started_at`
- **ExportController** — `json()` streams with `response()->stream()` + `lazy()`; `csv()` tab-prefixes cells starting with `=+-@` to prevent formula injection
- **AiController** — rate limited 20 req/min per user via `RateLimiter`
- **GitHubController** — caches repos 120s, PR/branch data 300s per project; uses per-user encrypted `github_token`
- **DashboardLayoutController** — validates widget list against whitelist: `['stats', 'recent_projects', 'activity_feed', 'kanban_preview', 'time_tracking']`

---

## Services

### AiAssistantService (`app/Services/AiAssistantService.php`)
```php
suggestTasks(Project $project, array $existingTasks): array  // 5 suggestions [{title, priority, description}]
chat(string $message, array $history, ?Project $project): string  // multi-turn
```
Config: `ANTHROPIC_API_KEY` → `config('services.anthropic.key')`

---

## Blade Views

### Layout hierarchy
```
layouts/app.blade.php       — authenticated shell (sidebar + navbar)
  layouts/partials/sidebar.blade.php
  layouts/partials/navbar.blade.php
layouts/guest.blade.php     — auth pages
```

### Pages
| View | Route |
|---|---|
| `dashboard.blade.php` | /dashboard |
| `projects/index.blade.php` | /projects |
| `projects/show.blade.php` | /projects/{project} (Kanban) |
| `projects/calendar.blade.php` | /projects/{project}/calendar |
| `projects/list.blade.php` | /projects/{project}/list |
| `projects/milestones.blade.php` | /projects/{project}/milestones |
| `settings.blade.php` | /settings |
| `search.blade.php` | /search |

### Partials
```
partials/activity-feed.blade.php
partials/ai-assistant.blade.php          AI chat modal
partials/dashboard-editor.blade.php      Widget reorder UI
partials/github-widget.blade.php
partials/kanban-preview.blade.php
partials/quick-task-modal.blade.php
partials/time-tracking-widget.blade.php
partials/widgets/*.blade.php             5 widget partials
```

---

## Frontend Patterns

### CSS (`resources/css/app.css`)
- Tailwind v4 — no `tailwind.config.js`; all config in `@theme {}` blocks with OKLch color tokens
- Dark mode: `@variant dark (&:is(.dark *))` — toggled by adding/removing `.dark` on `<html>`
- Never add a `tailwind.config.js`

### JS (`resources/js/app.js`) — 5 Alpine components

| Component | Purpose |
|---|---|
| `timerWidget` | Time tracking (start/stop, live display, entry list) |
| `githubWidget` | Repo browser, PR/branch viewer, link/unlink |
| `aiAssistant` | Chat + task suggestion modal |
| `projectTimeReport` | Time report modal per project |
| Global functions | `toggleMobileSidebar`, `toggleTheme`, `toggleUserMenu` |

Page-specific JS lives in `@push('scripts')` blocks at the bottom of each view.

### Passing server data to JS safely
```blade
{{-- Collections --}}
<script type="application/json" id="tasks-data">
    {!! json_encode($tasks, JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>

{{-- Small values --}}
<button data-task-id="{{ $task->id }}">...</button>
```
Never interpolate user strings directly into JS event handlers.

### Dark mode
```js
document.documentElement.classList.toggle('dark');
localStorage.setItem('theme', 'dark');
// Hydrate on load: if (localStorage.getItem('theme') === 'dark') { ... }
```

### CSRF in AJAX
```js
headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
```

### Kanban drag-drop (`projects/show.blade.php`)
- HTML5 native drag events; on drop fires `PUT /tasks/{id}` with `{status}`, then `location.reload()`
- Edit-task modal keeps `currentChecklists` JS array synced to the `<script type="application/json">` blob after each checklist AJAX call — no page reload, preserves unsaved edits

### File uploads
- Stored via `Storage::disk('public')` — avatars under `avatars/`, project images under `projects/`
- Client sends base64 (`picture_base64` / `avatar_base64`); server validates MIME with `finfo()`, enforces 2 MB (projects) / 4 MB (avatars)
- Always check: `$path = $file->store(...); abort_if($path === false, 500, '...');`
- Access in Blade: `asset('storage/' . $path)`

---

## Notable Patterns & Non-Standard Approaches

### POST instead of PUT for project update
`POST /projects/{project}/update` — browsers can't send `multipart/form-data` with PUT via HTML forms, and method spoofing breaks multipart uploads.

### PHP 8.3 attribute-based fillables
`User` model uses `#[Fillable([...])]` attribute; other models use `protected $fillable`.

### Pessimistic locking for timers
```php
DB::transaction(function () use ($task, $user) {
    $running = TimeEntry::where('user_id', $user->id)
        ->whereNull('ended_at')->lockForUpdate()->first();
});
```

### Streaming exports
```php
return response()->stream(function () use ($project) {
    $project->tasks()->lazy()->each(fn($task) => print(json_encode($task) . "\n"));
}, 200, [...]);
```

### Middleware & Providers
- `AppServiceProvider` registers `QuickTaskComposer` on `layouts.app` — injects `quickTaskProjects` for the sidebar quick-task modal (empty collection when unauthenticated)
- `auth` middleware on all feature routes; `guest` on auth routes

---

## Key Files Quick Reference

| What | Where |
|---|---|
| Feature routes | `routes/web.php` |
| Auth routes | `routes/auth.php` |
| All models | `app/Models/` |
| All controllers | `app/Http/Controllers/` |
| AI service | `app/Services/AiAssistantService.php` |
| View composer | `app/Http/View/Composers/QuickTaskComposer.php` |
| Main layout | `resources/views/layouts/app.blade.php` |
| Kanban board view | `resources/views/projects/show.blade.php` |
| Dashboard | `resources/views/dashboard.blade.php` |
| CSS (Tailwind config) | `resources/css/app.css` |
| JS (Alpine components) | `resources/js/app.js` |
| All migrations | `database/migrations/` |
| External services config | `config/services.php` |
| Dev commands | `composer.json` → `scripts` |

---

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- alpinejs (ALPINEJS) - v3

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
