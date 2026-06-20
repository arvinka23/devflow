<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Label;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaskComment;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@devflow.app')->firstOrFail();

        // ── Labels ──────────────────────────────────────────────────────────────
        $labelDefs = [
            ['name' => 'bug',         'color' => '#ef4444'],
            ['name' => 'feature',     'color' => '#6366f1'],
            ['name' => 'enhancement', 'color' => '#8b5cf6'],
            ['name' => 'design',      'color' => '#ec4899'],
            ['name' => 'backend',     'color' => '#10b981'],
            ['name' => 'frontend',    'color' => '#f59e0b'],
            ['name' => 'devops',      'color' => '#ef4444'],
            ['name' => 'urgent',      'color' => '#dc2626'],
            ['name' => 'docs',        'color' => '#64748b'],
            ['name' => 'testing',     'color' => '#0ea5e9'],
        ];

        $labels = [];
        foreach ($labelDefs as $def) {
            $labels[$def['name']] = Label::create(['user_id' => $user->id, ...$def]);
        }

        // ── Projects ────────────────────────────────────────────────────────────
        $projects = [
            [
                'name' => 'DevFlow App',
                'description' => 'The main project management platform. Building core features and polishing UI/UX.',
                'color' => '#6366f1',
                'status' => 'active',
                'tasks' => [
                    ['title' => 'Implement OAuth login (GitHub)',  'status' => 'done',        'priority' => 'high',   'days_ago' => 14, 'labels' => ['backend', 'feature'],    'comments' => ['Works perfectly with GitHub OAuth flow.', 'Tested on staging — all good!']],
                    ['title' => 'Design onboarding flow',          'status' => 'done',        'priority' => 'medium', 'days_ago' => 10, 'labels' => ['design'],                'comments' => ['Figma prototype approved by stakeholders.']],
                    ['title' => 'Build Kanban drag-and-drop',      'status' => 'done',        'priority' => 'high',   'days_ago' => 7,  'labels' => ['frontend', 'feature'],   'comments' => ['Used HTML5 native drag events — no extra deps.']],
                    ['title' => 'Add AI task suggestions',         'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 2,  'labels' => ['feature', 'backend'],    'comments' => ['Anthropic API integration looks solid.', 'Rate limiting added — 20 req/min per user.']],
                    ['title' => 'Write API documentation',         'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 1,  'labels' => ['docs'],                  'comments' => ['Using OpenAPI 3.1 spec format.']],
                    ['title' => 'Time tracking weekly chart',      'status' => 'todo',        'priority' => 'medium', 'due_days' => 3,  'labels' => ['frontend', 'feature'],   'comments' => []],
                    ['title' => 'Mobile responsive audit',         'status' => 'todo',        'priority' => 'low',    'due_days' => 7,  'labels' => ['frontend'],              'comments' => ['Consider using Playwright for visual regression.']],
                    ['title' => 'Dark mode polish',                'status' => 'todo',        'priority' => 'low',    'due_days' => 10, 'labels' => ['design', 'frontend'],    'comments' => []],
                ],
            ],
            [
                'name' => 'E-Commerce Redesign',
                'description' => 'Full redesign of the storefront — new checkout flow, product pages and mobile-first layout.',
                'color' => '#f59e0b',
                'status' => 'active',
                'tasks' => [
                    ['title' => 'User research interviews',         'status' => 'done',        'priority' => 'high',   'days_ago' => 20, 'labels' => ['design'],              'comments' => ['Interviewed 12 users — clear pain points with checkout.']],
                    ['title' => 'Wireframes for product page',      'status' => 'done',        'priority' => 'high',   'days_ago' => 15, 'labels' => ['design'],              'comments' => ['Approved after two revision rounds.']],
                    ['title' => 'Design system tokens',             'status' => 'done',        'priority' => 'medium', 'days_ago' => 12, 'labels' => ['design', 'frontend'],  'comments' => []],
                    ['title' => 'Homepage hero redesign',           'status' => 'done',        'priority' => 'high',   'days_ago' => 8,  'labels' => ['design', 'frontend'],  'comments' => ['A/B test shows +18% engagement.']],
                    ['title' => 'Implement new checkout flow',      'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 3,  'labels' => ['frontend', 'backend'], 'comments' => ['Stripe integration in progress.', 'Need to handle 3DS2 redirects.']],
                    ['title' => 'Product image gallery component',  'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 1,  'labels' => ['frontend'],            'comments' => ['Using lazy loading for perf.']],
                    ['title' => 'Cart micro-animations',            'status' => 'todo',        'priority' => 'low',    'due_days' => 5,  'labels' => ['frontend'],            'comments' => []],
                    ['title' => 'A/B test CTA button colour',       'status' => 'todo',        'priority' => 'medium', 'due_days' => 8,  'labels' => ['design', 'testing'],   'comments' => []],
                    ['title' => 'SEO meta tags audit',              'status' => 'todo',        'priority' => 'low',    'due_days' => 14, 'labels' => ['docs'],                'comments' => []],
                ],
            ],
            [
                'name' => 'Mobile Banking App',
                'description' => 'iOS & Android fintech app — account dashboard, transfers, spending analytics.',
                'color' => '#10b981',
                'status' => 'active',
                'tasks' => [
                    ['title' => 'PSD2 compliance review',           'status' => 'done',        'priority' => 'high',   'days_ago' => 30, 'labels' => ['backend', 'urgent'],    'comments' => ['Legal team sign-off received.']],
                    ['title' => 'Biometric auth integration',        'status' => 'done',        'priority' => 'high',   'days_ago' => 21, 'labels' => ['backend', 'feature'],   'comments' => ['Face ID + Touch ID both working.']],
                    ['title' => 'Transaction list view',             'status' => 'done',        'priority' => 'medium', 'days_ago' => 14, 'labels' => ['frontend'],             'comments' => []],
                    ['title' => 'Spending pie chart widget',         'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 4,  'labels' => ['frontend', 'feature'],  'comments' => ['Using D3.js for the chart.']],
                    ['title' => 'Push notification service',         'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 2,  'labels' => ['backend', 'feature'],   'comments' => ['Firebase FCM configured for both platforms.', 'Need to handle background delivery on iOS.']],
                    ['title' => 'International transfer flow',       'status' => 'todo',        'priority' => 'high',   'due_days' => 2,  'labels' => ['backend', 'urgent'],    'comments' => []],
                    ['title' => 'Card freeze / unfreeze UI',         'status' => 'todo',        'priority' => 'medium', 'due_days' => 6,  'labels' => ['frontend'],             'comments' => []],
                    ['title' => 'Accessibility audit (WCAG 2.2)',    'status' => 'todo',        'priority' => 'low',    'due_days' => 10, 'labels' => ['testing'],              'comments' => ['Lighthouse currently scores 74 — target 95+.']],
                ],
            ],
            [
                'name' => 'SaaS Dashboard v2',
                'description' => 'Rebuilding the analytics dashboard with real-time charts, custom widgets and team views.',
                'color' => '#8b5cf6',
                'status' => 'active',
                'tasks' => [
                    ['title' => 'Migrate from Chart.js to Recharts',  'status' => 'done',        'priority' => 'high',   'days_ago' => 10, 'labels' => ['frontend'],             'comments' => ['Bundle size down 40KB after migration.']],
                    ['title' => 'Real-time WebSocket feed',            'status' => 'done',        'priority' => 'high',   'days_ago' => 7,  'labels' => ['backend', 'feature'],   'comments' => ['Laravel Reverb used for broadcasting.']],
                    ['title' => 'Custom date range picker',            'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 2,  'labels' => ['frontend'],             'comments' => ['Flatpickr vs custom Alpine component — going custom.']],
                    ['title' => 'Export to CSV / PDF',                 'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 1,  'labels' => ['feature'],              'comments' => []],
                    ['title' => 'Team member activity heatmap',        'status' => 'todo',        'priority' => 'low',    'due_days' => 7,  'labels' => ['frontend', 'design'],   'comments' => []],
                    ['title' => 'Embed dashboard (iframe widget)',      'status' => 'todo',        'priority' => 'low',    'due_days' => 14, 'labels' => ['feature'],              'comments' => []],
                ],
            ],
            [
                'name' => 'Internal DevOps Tooling',
                'description' => 'CI/CD pipeline improvements, deployment scripts and developer experience upgrades.',
                'color' => '#ef4444',
                'status' => 'active',
                'tasks' => [
                    ['title' => 'Migrate CI to GitHub Actions',     'status' => 'done',        'priority' => 'high',   'days_ago' => 25, 'labels' => ['devops'],              'comments' => ['Build time improved from 12 min to 4 min.']],
                    ['title' => 'Docker-compose local dev env',     'status' => 'done',        'priority' => 'high',   'days_ago' => 18, 'labels' => ['devops'],              'comments' => ['All team members onboarded — zero setup issues.']],
                    ['title' => 'Auto-deploy to staging on PR',     'status' => 'done',        'priority' => 'medium', 'days_ago' => 12, 'labels' => ['devops', 'feature'],   'comments' => []],
                    ['title' => 'Secrets rotation script',          'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 3,  'labels' => ['devops', 'urgent'],    'comments' => ['Using AWS Secrets Manager — rotation tested in staging.']],
                    ['title' => 'Centralised logging (Loki)',        'status' => 'todo',        'priority' => 'medium', 'due_days' => 4,  'labels' => ['devops'],              'comments' => []],
                    ['title' => 'Performance regression alerts',    'status' => 'todo',        'priority' => 'low',    'due_days' => 9,  'labels' => ['devops', 'testing'],   'comments' => []],
                ],
            ],
            [
                'name' => 'Marketing Site Refresh',
                'description' => 'New landing pages, blog engine migration and SEO overhaul for Q3 growth target.',
                'color' => '#f97316',
                'status' => 'active',
                'tasks' => [
                    ['title' => 'Content audit existing pages',     'status' => 'done',        'priority' => 'medium', 'days_ago' => 22, 'labels' => ['docs'],                'comments' => ['32 pages audited — 8 flagged for rewrite.']],
                    ['title' => 'New pricing page design',          'status' => 'done',        'priority' => 'high',   'days_ago' => 15, 'labels' => ['design'],              'comments' => ['Conversion rate up 22% in A/B test.']],
                    ['title' => 'Blog migration to Astro',          'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 5,  'labels' => ['frontend'],            'comments' => ['70 posts migrated, 30 remaining.']],
                    ['title' => 'Case study template',              'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 2,  'labels' => ['design', 'docs'],      'comments' => []],
                    ['title' => 'Integrate Fathom analytics',       'status' => 'todo',        'priority' => 'low',    'due_days' => 5,  'labels' => ['frontend'],            'comments' => []],
                    ['title' => 'Schema markup for blog posts',     'status' => 'todo',        'priority' => 'medium', 'due_days' => 8,  'labels' => ['docs'],                'comments' => []],
                    ['title' => 'Launch announcement email',        'status' => 'todo',        'priority' => 'high',   'due_days' => 1,  'labels' => ['urgent'],              'comments' => ['Draft ready — needs final approval.']],
                ],
            ],
            [
                'name' => 'Open Source UI Library',
                'description' => 'Component library built on Tailwind v4 + Alpine — buttons, modals, forms, data tables.',
                'color' => '#06b6d4',
                'status' => 'on-hold',
                'tasks' => [
                    ['title' => 'Button component variants',        'status' => 'done',        'priority' => 'high',   'days_ago' => 40, 'labels' => ['frontend', 'feature'],  'comments' => ['12 variants shipped including loading state.']],
                    ['title' => 'Form input & validation states',   'status' => 'done',        'priority' => 'high',   'days_ago' => 35, 'labels' => ['frontend'],             'comments' => []],
                    ['title' => 'Modal & drawer components',        'status' => 'done',        'priority' => 'medium', 'days_ago' => 28, 'labels' => ['frontend'],             'comments' => ['Focus trap and ARIA attributes implemented.']],
                    ['title' => 'Data table with sorting',          'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 15, 'labels' => ['frontend', 'feature'],  'comments' => ['Pagination and column resizing next.']],
                    ['title' => 'Storybook documentation site',    'status' => 'todo',        'priority' => 'low',    'due_days' => 30, 'labels' => ['docs'],                 'comments' => []],
                    ['title' => 'Publish to npm',                  'status' => 'todo',        'priority' => 'high',   'due_days' => 21, 'labels' => ['devops'],               'comments' => []],
                ],
            ],
            [
                'name' => 'Client Portal (Acme Corp)',
                'description' => 'Secure client-facing portal for document sharing, project status and invoice management.',
                'color' => '#84cc16',
                'status' => 'active',
                'tasks' => [
                    ['title' => 'Role-based access control',        'status' => 'done',        'priority' => 'high',   'days_ago' => 18, 'labels' => ['backend', 'feature'],   'comments' => ['Three roles: admin, manager, viewer.']],
                    ['title' => 'Document upload & preview',        'status' => 'done',        'priority' => 'high',   'days_ago' => 12, 'labels' => ['backend', 'frontend'],  'comments' => ['PDF preview using pdf.js.']],
                    ['title' => 'Invoice PDF generation',           'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 3,  'labels' => ['backend'],              'comments' => ['Dompdf integration — handling multi-page layout.']],
                    ['title' => 'Email notification on upload',     'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 1,  'labels' => ['backend'],              'comments' => []],
                    ['title' => 'Two-factor authentication',        'status' => 'todo',        'priority' => 'high',   'due_days' => 2,  'labels' => ['backend', 'urgent'],    'comments' => ['TOTP (Google Authenticator) preferred by client.']],
                    ['title' => 'Audit log for client actions',     'status' => 'todo',        'priority' => 'medium', 'due_days' => 5,  'labels' => ['backend'],              'comments' => []],
                    ['title' => 'White-label theming support',      'status' => 'todo',        'priority' => 'low',    'due_days' => 14, 'labels' => ['design', 'feature'],    'comments' => []],
                ],
            ],
        ];

        $now = Carbon::now();

        foreach ($projects as $projectData) {
            $taskDefs = $projectData['tasks'];
            unset($projectData['tasks']);

            $project = Project::create([
                ...$projectData,
                'user_id' => $user->id,
            ]);

            ActivityLog::log($user, 'project.created', 'created project "'.$project->name.'"', $project);

            foreach ($taskDefs as $index => $taskDef) {
                $dueDate = null;
                if (isset($taskDef['due_days'])) {
                    $dueDate = $now->copy()->addDays($taskDef['due_days'])->toDateString();
                } elseif (isset($taskDef['days_ago']) && $taskDef['status'] !== 'done') {
                    $dueDate = $now->copy()->addDays(rand(3, 14))->toDateString();
                }

                $task = Task::create([
                    'project_id' => $project->id,
                    'title' => $taskDef['title'],
                    'status' => $taskDef['status'],
                    'priority' => $taskDef['priority'],
                    'order' => $index,
                    'due_date' => $dueDate,
                    'description' => null,
                ]);

                // ── Labels ─────────────────────────────────────────────────────
                $labelIds = array_filter(
                    array_map(fn ($name) => $labels[$name]->id ?? null, $taskDef['labels'] ?? [])
                );
                if ($labelIds) {
                    $task->labels()->sync($labelIds);
                }

                // ── Comments ───────────────────────────────────────────────────
                foreach ($taskDef['comments'] ?? [] as $body) {
                    TaskComment::create([
                        'task_id' => $task->id,
                        'user_id' => $user->id,
                        'body' => $body,
                    ]);
                }

                // ── Checklists for in-progress tasks ───────────────────────────
                if ($task->status === 'in_progress') {
                    $checklists = match (true) {
                        str_contains(strtolower($task->title), 'checkout') => ['Design form layout', 'Implement validation', 'Connect payment gateway', 'Write tests'],
                        str_contains(strtolower($task->title), 'auth') => ['Research provider options', 'Backend callback route', 'Frontend button', 'Test with test account'],
                        str_contains(strtolower($task->title), 'migration') => ['Backup existing data', 'Run migration script', 'Verify data integrity', 'Update docs'],
                        str_contains(strtolower($task->title), 'api') => ['Define endpoints', 'Write OpenAPI spec', 'Add code examples', 'Publish to docs site'],
                        default => ['Research & planning', 'Implementation', 'Review & QA'],
                    };
                    foreach ($checklists as $ci => $title) {
                        TaskChecklist::create([
                            'task_id' => $task->id,
                            'title' => $title,
                            'completed' => $ci === 0,
                        ]);
                    }
                }

                // ── Activity log ───────────────────────────────────────────────
                $createdAt = $now->copy()->subDays($taskDef['days_ago'] ?? 2)->subHours(rand(0, 8));

                ActivityLog::create([
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'task_id' => $task->id,
                    'event' => 'task.created',
                    'subject' => 'created task "'.$task->title.'"',
                    'meta' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($task->status === 'done') {
                    $completedAt = $createdAt->copy()->addHours(rand(2, 48));
                    ActivityLog::create([
                        'user_id' => $user->id,
                        'project_id' => $project->id,
                        'task_id' => $task->id,
                        'event' => 'task.completed',
                        'subject' => 'completed "'.$task->title.'"',
                        'meta' => null,
                        'created_at' => $completedAt,
                        'updated_at' => $completedAt,
                    ]);
                } elseif ($task->status === 'in_progress') {
                    $movedAt = $createdAt->copy()->addHours(rand(1, 24));
                    ActivityLog::create([
                        'user_id' => $user->id,
                        'project_id' => $project->id,
                        'task_id' => $task->id,
                        'event' => 'task.status_changed',
                        'subject' => 'moved "'.$task->title.'" to In Progress',
                        'meta' => ['old_status' => 'todo', 'new_status' => 'in_progress'],
                        'created_at' => $movedAt,
                        'updated_at' => $movedAt,
                    ]);
                }

                // ── Time entries ───────────────────────────────────────────────
                if (in_array($task->status, ['done', 'in_progress'])) {
                    $numSessions = rand(1, 3);
                    for ($s = 0; $s < $numSessions; $s++) {
                        $sessionStart = $createdAt->copy()->addHours(rand(1, 20) + ($s * 24));
                        $duration = rand(1200, 7200);
                        $sessionEnd = $sessionStart->copy()->addSeconds($duration);

                        $isRunning = ($task->status === 'in_progress' && $s === $numSessions - 1 && rand(0, 2) === 0);

                        TimeEntry::create([
                            'task_id' => $task->id,
                            'user_id' => $user->id,
                            'started_at' => $sessionStart,
                            'ended_at' => $isRunning ? null : $sessionEnd,
                            'duration_seconds' => $isRunning ? null : $duration,
                        ]);
                    }
                }
            }
        }

        $this->command->info('Demo data seeded for demo@devflow.app');
        $this->command->info('Projects: '.count($projects));
        $totalTasks = array_sum(array_map(fn ($p) => count($p['tasks']), $projects));
        $this->command->info('Tasks: ~'.$totalTasks);
        $this->command->info('Labels: '.count($labelDefs));
    }
}
