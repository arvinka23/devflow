<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'profile_picture', 'github_token', 'dashboard_layout', 'notification_preferences'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'github_token'             => 'encrypted',
            'dashboard_layout'         => 'array',
            'notification_preferences' => 'array',
        ];
    }

    public static function getDefaultLayout(): array
    {
        return ['stats', 'recent_projects', 'activity_feed', 'kanban_preview', 'time_tracking'];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function notificationPreference(string $key, bool $default = true): bool
    {
        return (bool) ($this->notification_preferences[$key] ?? $default);
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        $first = strtoupper(substr($words[0] ?? '', 0, 1));
        $second = strtoupper(substr($words[1] ?? '', 0, 1));
        return $first . $second ?: '?';
    }
}
