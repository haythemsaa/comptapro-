<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineAlert extends Notification
{
    use Queueable;

    private array $alert;

    public function __construct(array $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Canaux de notification
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Message email
     */
    public function toMail($notifiable): MailMessage
    {
        $severity = $this->alert['severity'] === 'high' ? '🔴 URGENT' : '⚠️';

        return (new MailMessage)
            ->subject("{$severity} Deadline à venir - {$this->alert['type']}")
            ->greeting('Bonjour,')
            ->line($this->alert['message'])
            ->line("Date limite: {$this->alert['due_date']}")
            ->line("Jours restants: {$this->alert['days_left']}")
            ->action('Voir dans ComptaPro', url('/automation/dashboard'))
            ->line('Merci d\'utiliser ComptaPro Tunisia!');
    }

    /**
     * Représentation tableau (pour database)
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'deadline',
            'alert' => $this->alert,
            'severity' => $this->alert['severity'],
            'message' => $this->alert['message'],
        ];
    }
}
