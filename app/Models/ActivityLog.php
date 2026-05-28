<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'project_id', 'task_id', 'event', 'subject', 'meta'];

    protected $casts = ['meta' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public static function log(
        User $user,
        string $event,
        string $subject,
        ?Project $project = null,
        ?Task $task = null,
        array $meta = []
    ): self {
        return self::create([
            'user_id'    => $user->id,
            'project_id' => $project?->id,
            'task_id'    => $task?->id,
            'event'      => $event,
            'subject'    => $subject,
            'meta'       => $meta ?: null,
        ]);
    }
}
