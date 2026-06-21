<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $url) {}  // ✅ يستقبل الرابط

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->greeting('Hello!')
            ->line('You requested to reset your password.')
            ->action('Reset Password', $this->url)
            ->line('This link expires in 60 minutes.')
            ->line('If you did not request this, ignore this email.');
    }
}
