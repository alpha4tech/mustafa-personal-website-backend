<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Contact;
use Carbon\Carbon;

class NotificationService
{
   public function createContactNotification(Contact $contact): void
    {
        AdminNotification::create([
            'type'           => 'contact',
            'title'          => 'رسالة جديدة من المستخدمين',
            'message'        => "رسالة جديدة من: {$contact->name} — {$contact->subject}",
            'icon'           => 'bi bi-envelope-fill',
            'color'          => 'info',
            'reference_id'   => $contact->id,
            'reference_type' => 'contact',
        ]);
    }

    private function createIfNotExists(
        string $type,
        int $referenceId,
        string $referenceType,
        string $title,
        string $message,
        string $icon,
        string $color,
    ): void {
        $exists = AdminNotification::where('type', $type)
            ->where('reference_id', $referenceId)
            ->where('reference_type', $referenceType)
            ->whereNull('read_at')
            ->exists();

        if (!$exists) {
            AdminNotification::create([
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'icon'           => $icon,
                'color'          => $color,
                'reference_id'   => $referenceId,
                'reference_type' => $referenceType,
            ]);
        }
    }
}
