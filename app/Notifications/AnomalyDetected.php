<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnomalyDetected extends Notification
{
    use Queueable;

    private array $anomalies;
    private int $totalCount;

    public function __construct(array $anomalies, int $totalCount)
    {
        $this->anomalies = $anomalies;
        $this->totalCount = $totalCount;
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
        $message = (new MailMessage)
            ->subject("🔍 {$this->totalCount} anomalie(s) détectée(s)")
            ->greeting('Bonjour,')
            ->line("L'IA a détecté {$this->totalCount} anomalie(s) dans votre comptabilité:");

        foreach ($this->anomalies as $anomaly) {
            $icon = $anomaly['severity'] === 'high' ? '🔴' : '⚠️';
            $message->line("{$icon} {$anomaly['description']}");
        }

        return $message
            ->action('Voir les détails', url('/automation/dashboard'))
            ->line('Merci d\'utiliser ComptaPro Tunisia!');
    }

    /**
     * Représentation tableau
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'anomaly',
            'total_count' => $this->totalCount,
            'anomalies' => $this->anomalies,
        ];
    }
}
