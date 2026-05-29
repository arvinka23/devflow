<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Milestone extends Model
{
    protected $fillable = ['project_id', 'title', 'due_date'];

    protected $casts = ['due_date' => 'date'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getProgressAttribute(): int
    {
        // Use pre-loaded withCount values when available (avoids 2 extra queries per milestone).
        $total = array_key_exists('tasks_count', $this->attributes)
            ? (int) $this->attributes['tasks_count']
            : $this->tasks()->count();

        if ($total === 0) return 0;

        $done = array_key_exists('done_count', $this->attributes)
            ? (int) $this->attributes['done_count']
            : $this->tasks()->where('status', 'done')->count();

        return (int) round($done / $total * 100);
    }
}
