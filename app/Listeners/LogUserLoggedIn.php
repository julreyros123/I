<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;

class LogUserLoggedIn
{
    public function handle(Login $event): void
    {
        ActivityLog::create([
            'user_id' => $event->user->id ?? null,
            'module' => 'Auth',
            'action' => 'LOGIN',
            'description' => sprintf('%s logged in', $event->user->name ?? 'Unknown user'),
            'target_type' => get_class($event->user),
            'target_id' => $event->user->id ?? null,
            'meta' => [
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
                'remember' => $event->remember,
            ],
        ]);
    }
}
