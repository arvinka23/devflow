<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'arvinka604@gmail.com')->firstOrFail();

        $projects = [
            [
                'name'        => 'DevFlow App',
                'description' => 'The main project management platform. Building core features and polishing UI/UX.',
                'color'       => '#6366f1',
                'status'      => 'active',
                'tasks' => [
                    ['title' => 'Implement OAuth login (GitHub)', 'status' => 'done',        'priority' => 'high',   'days_ago' => 14],
                    ['title' => 'Design onboarding flow',         'status' => 'done',        'priority' => 'medium', 'days_ago' => 10],
                    ['title' => 'Build Kanban drag-and-drop',     'status' => 'done',        'priority' => 'high',   'days_ago' => 7],
                    ['title' => 'Add AI task suggestions',        'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 2],
                    ['title' => 'Write API documentation',        'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 1],
                    ['title' => 'Time tracking weekly chart',     'status' => 'todo',        'priority' => 'medium', 'due_days' => 3],
                    ['title' => 'Mobile responsive audit',        'status' => 'todo',        'priority' => 'low',    'due_days' => 7],
                    ['title' => 'Dark mode polish',               'status' => 'todo',        'priority' => 'low',    'due_days' => 10],
                ],
            ],
            [
                'name'        => 'E-Commerce Redesign',
                'description' => 'Full redesign of the storefront — new checkout flow, product pages and mobile-first layout.',
                'color'       => '#f59e0b',
                'status'      => 'active',
                'tasks' => [
                    ['title' => 'User research interviews',         'status' => 'done',        'priority' => 'high',   'days_ago' => 20],
                    ['title' => 'Wireframes for product page',      'status' => 'done',        'priority' => 'high',   'days_ago' => 15],
                    ['title' => 'Design system tokens',             'status' => 'done',        'priority' => 'medium', 'days_ago' => 12],
                    ['title' => 'Homepage hero redesign',           'status' => 'done',        'priority' => 'high',   'days_ago' => 8],
                    ['title' => 'Implement new checkout flow',      'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 3],
                    ['title' => 'Product image gallery component',  'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 1],
                    ['title' => 'Cart micro-animations',            'status' => 'todo',        'priority' => 'low',    'due_days' => 5],
                    ['title' => 'A/B test CTA button colour',       'status' => 'todo',        'priority' => 'medium', 'due_days' => 8],
                    ['title' => 'SEO meta tags audit',              'status' => 'todo',        'priority' => 'low',    'due_days' => 14],
                ],
            ],
            [
                'name'        => 'Mobile Banking App',
                'description' => 'iOS & Android fintech app — account dashboard, transfers, spending analytics.',
                'color'       => '#10b981',
                'status'      => 'active',
                'tasks' => [
                    ['title' => 'PSD2 compliance review',           'status' => 'done',        'priority' => 'high',   'days_ago' => 30],
                    ['title' => 'Biometric auth integration',       'status' => 'done',        'priority' => 'high',   'days_ago' => 21],
                    ['title' => 'Transaction list view',            'status' => 'done',        'priority' => 'medium', 'days_ago' => 14],
                    ['title' => 'Spending pie chart widget',        'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 4],
                    ['title' => 'Push notification service',        'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 2],
                    ['title' => 'International transfer flow',      'status' => 'todo',        'priority' => 'high',   'due_days' => 2],
                    ['title' => 'Card freeze / unfreeze UI',        'status' => 'todo',        'priority' => 'medium', 'due_days' => 6],
                    ['title' => 'Accessibility audit (WCAG 2.2)',   'status' => 'todo',        'priority' => 'low',    'due_days' => 10],
                ],
            ],
            [
                'name'        => 'SaaS Dashboard v2',
                'description' => 'Rebuilding the analytics dashboard with real-time charts, custom widgets and team views.',
                'color'       => '#8b5cf6',
                'status'      => 'active',
                'tasks' => [
                    ['title' => 'Migrate from Chart.js to Recharts', 'status' => 'done',        'priority' => 'high',   'days_ago' => 10],
                    ['title' => 'Real-time WebSocket feed',           'status' => 'done',        'priority' => 'high',   'days_ago' => 7],
                    ['title' => 'Custom date range picker',           'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 2],
                    ['title' => 'Export to CSV / PDF',                'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 1],
                    ['title' => 'Team member activity heatmap',       'status' => 'todo',        'priority' => 'low',    'due_days' => 7],
                    ['title' => 'Embed dashboard (iframe widget)',     'status' => 'todo',        'priority' => 'low',    'due_days' => 14],
                ],
            ],
            [
                'name'        => 'Internal DevOps Tooling',
                'description' => 'CI/CD pipeline improvements, deployment scripts and developer experience upgrades.',
                'color'       => '#ef4444',
                'status'      => 'active',
                'tasks' => [
                    ['title' => 'Migrate CI to GitHub Actions',     'status' => 'done',        'priority' => 'high',   'days_ago' => 25],
                    ['title' => 'Docker-compose local dev env',     'status' => 'done',        'priority' => 'high',   'days_ago' => 18],
                    ['title' => 'Auto-deploy to staging on PR',     'status' => 'done',        'priority' => 'medium', 'days_ago' => 12],
                    ['title' => 'Secrets rotation script',          'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 3],
                    ['title' => 'Centralised logging (Loki)',       'status' => 'todo',        'priority' => 'medium', 'due_days' => 4],
                    ['title' => 'Performance regression alerts',    'status' => 'todo',        'priority' => 'low',    'due_days' => 9],
                ],
            ],
            [
                'name'        => 'Marketing Site Refresh',
                'description' => 'New landing pages, blog engine migration and SEO overhaul for Q3 growth target.',
                'color'       => '#f97316',
                'status'      => 'active',
                'tasks' => [
                    ['title' => 'Content audit existing pages',     'status' => 'done',        'priority' => 'medium', 'days_ago' => 22],
                    ['title' => 'New pricing page design',          'status' => 'done',        'priority' => 'high',   'days_ago' => 15],
                    ['title' => 'Blog migration to Astro',          'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 5],
                    ['title' => 'Case study template',              'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 2],
                    ['title' => 'Integrate Fathom analytics',       'status' => 'todo',        'priority' => 'low',    'due_days' => 5],
                    ['title' => 'Schema markup for blog posts',     'status' => 'todo',        'priority' => 'medium', 'due_days' => 8],
                    ['title' => 'Launch announcement email',        'status' => 'todo',        'priority' => 'high',   'due_days' => 1],
                ],
            ],
            [
                'name'        => 'Open Source UI Library',
                'description' => 'Component library built on Tailwind v4 + Alpine — buttons, modals, forms, data tables.',
                'color'       => '#06b6d4',
                'status'      => 'on-hold',
                'tasks' => [
                    ['title' => 'Button component variants',        'status' => 'done',        'priority' => 'high',   'days_ago' => 40],
                    ['title' => 'Form input & validation states',   'status' => 'done',        'priority' => 'high',   'days_ago' => 35],
                    ['title' => 'Modal & drawer components',        'status' => 'done',        'priority' => 'medium', 'days_ago' => 28],
                    ['title' => 'Data table with sorting',          'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 15],
                    ['title' => 'Storybook documentation site',    'status' => 'todo',        'priority' => 'low',    'due_days' => 30],
                    ['title' => 'Publish to npm',                  'status' => 'todo',        'priority' => 'high',   'due_days' => 21],
                ],
            ],
            [
                'name'        => 'Client Portal (Acme Corp)',
                'description' => 'Secure client-facing portal for document sharing, project status and invoice management.',
                'color'       => '#84cc16',
                'status'      => 'active',
                'tasks' => [
                    ['title' => 'Role-based access control',        'status' => 'done',        'priority' => 'high',   'days_ago' => 18],
                    ['title' => 'Document upload & preview',        'status' => 'done',        'priority' => 'high',   'days_ago' => 12],
                    ['title' => 'Invoice PDF generation',           'status' => 'in_progress', 'priority' => 'high',   'days_ago' => 3],
                    ['title' => 'Email notification on upload',     'status' => 'in_progress', 'priority' => 'medium', 'days_ago' => 1],
                    ['title' => 'Two-factor authentication',        'status' => 'todo',        'priority' => 'high',   'due_days' => 2],
                    ['title' => 'Audit log for client actions',     'status' => 'todo',        'priority' => 'medium', 'due_days' => 5],
                    ['title' => 'White-label theming support',      'status' => 'todo',        'priority' => 'low',    'due_days' => 14],
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

            ActivityLog::log($user, 'project.created', 'created project "' . $project->name . '"', $project);

            foreach ($taskDefs as $index => $taskDef) {
                $dueDate = null;
                if (isset($taskDef['due_days'])) {
                    $dueDate = $now->copy()->addDays($taskDef['due_days'])->toDateString();
                } elseif (isset($taskDef['days_ago']) && $taskDef['status'] !== 'done') {
                    $dueDate = $now->copy()->addDays(rand(3, 14))->toDateString();
                }

                $task = Task::create([
                    'project_id' => $project->id,
                    'title'      => $taskDef['title'],
                    'status'     => $taskDef['status'],
                    'priority'   => $taskDef['priority'],
                    'order'      => $index,
                    'due_date'   => $dueDate,
                    'description' => null,
                ]);

                // Add checklists to in-progress tasks
                if ($task->status === 'in_progress') {
                    $checklists = match(true) {
                        str_contains(strtolower($task->title), 'checkout')   => ['Design form layout', 'Implement validation', 'Connect payment gateway', 'Write tests'],
                        str_contains(strtolower($task->title), 'auth')       => ['Research provider options', 'Backend callback route', 'Frontend button', 'Test with test account'],
                        str_contains(strtolower($task->title), 'migration')  => ['Backup existing data', 'Run migration script', 'Verify data integrity', 'Update docs'],
                        str_contains(strtolower($task->title), 'api')        => ['Define endpoints', 'Write OpenAPI spec', 'Add code examples', 'Publish to docs site'],
                        default                                               => ['Research & planning', 'Implementation', 'Review & QA'],
                    };
                    foreach ($checklists as $ci => $title) {
                        TaskChecklist::create([
                            'task_id'   => $task->id,
                            'title'     => $title,
                            'completed' => $ci === 0, // first item done
                        ]);
                    }
                }

                // Activity log entries
                $createdAt = $now->copy()->subDays($taskDef['days_ago'] ?? 2)->subHours(rand(0, 8));

                $logEntry = ActivityLog::create([
                    'user_id'    => $user->id,
                    'project_id' => $project->id,
                    'task_id'    => $task->id,
                    'event'      => 'task.created',
                    'subject'    => 'created task "' . $task->title . '"',
                    'meta'       => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($task->status === 'done') {
                    $completedAt = $createdAt->copy()->addHours(rand(2, 48));
                    ActivityLog::create([
                        'user_id'    => $user->id,
                        'project_id' => $project->id,
                        'task_id'    => $task->id,
                        'event'      => 'task.completed',
                        'subject'    => 'completed "' . $task->title . '"',
                        'meta'       => null,
                        'created_at' => $completedAt,
                        'updated_at' => $completedAt,
                    ]);
                } elseif ($task->status === 'in_progress') {
                    $movedAt = $createdAt->copy()->addHours(rand(1, 24));
                    ActivityLog::create([
                        'user_id'    => $user->id,
                        'project_id' => $project->id,
                        'task_id'    => $task->id,
                        'event'      => 'task.status_changed',
                        'subject'    => 'moved "' . $task->title . '" to In Progress',
                        'meta'       => ['old_status' => 'todo', 'new_status' => 'in_progress'],
                        'created_at' => $movedAt,
                        'updated_at' => $movedAt,
                    ]);
                }

                // Time entries for done + in_progress tasks
                if (in_array($task->status, ['done', 'in_progress'])) {
                    $numSessions = rand(1, 3);
                    for ($s = 0; $s < $numSessions; $s++) {
                        $sessionStart = $createdAt->copy()->addHours(rand(1, 20) + ($s * 24));
                        $duration     = rand(1200, 7200); // 20 min – 2 hr
                        $sessionEnd   = $sessionStart->copy()->addSeconds($duration);

                        // Last session for in_progress: sometimes still running
                        $isRunning = ($task->status === 'in_progress' && $s === $numSessions - 1 && rand(0, 2) === 0);

                        TimeEntry::create([
                            'task_id'          => $task->id,
                            'user_id'          => $user->id,
                            'started_at'       => $sessionStart,
                            'ended_at'         => $isRunning ? null : $sessionEnd,
                            'duration_seconds' => $isRunning ? null : $duration,
                        ]);
                    }
                }
            }
        }

        $this->command->info('Demo data seeded for arvinka604@gmail.com');
        $this->command->info('Projects: ' . count($projects));
        $totalTasks = array_sum(array_map(fn($p) => count($p['tasks']), $projects));
        $this->command->info('Tasks: ~' . $totalTasks);
    }
}
