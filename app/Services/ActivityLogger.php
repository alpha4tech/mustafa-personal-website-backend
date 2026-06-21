<?php
namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    private static array $iconMap = [
        'post_created'   => ['icon' => 'bi-pencil',    'type' => 'blue'],
        'post_updated'   => ['icon' => 'bi-pencil',    'type' => 'blue'],
        'post_deleted'   => ['icon' => 'bi-trash',     'type' => 'red'],
        'message_read'   => ['icon' => 'bi-envelope',  'type' => 'green'],
        'project_created'=> ['icon' => 'bi-briefcase', 'type' => 'amber'],
        'project_updated'=> ['icon' => 'bi-briefcase', 'type' => 'amber'],
        'service_created'=> ['icon' => 'bi-gear',      'type' => 'purple'],
        'service_updated'=> ['icon' => 'bi-gear',      'type' => 'purple'],
        'profile_updated'=> ['icon' => 'bi-person',    'type' => 'teal'],
        'login'          => ['icon' => 'bi-box-arrow-in-right', 'type' => 'green'],
    ];

    public static function log(string $event, string $action, $subject = null): void
    {
        $meta = self::$iconMap[$event] ?? ['icon' => 'bi-activity', 'type' => 'blue'];

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'type'         => $meta['type'],
            'icon'         => $meta['icon'],
            'action'       => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->id,
        ]);
    }
}
