<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Logout;

class LogUserLoggedOut
{
    public function handle(Logout $event): void
    {
        ActivityLog::create([
            'user_id' => $event->user->id ?? null,
            'module' => 'Auth',
            'action' => 'LOGOUT',
            'description' => sprintf('%s logged out', $event->user->name ?? 'Unknown user'),
            'target_type' => get_class($event->user),
            'target_id' => $event->user->id ?? null,
            'meta' => [
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
            ],
        ]);
    }
}
