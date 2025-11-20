<?php

namespace App\Notifications\Belgium;

use App\Models\Belgium\VATDeclarationBE;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VATDeadlineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public VATDeclarationBE $declaration,
        public int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $urgency = $this->daysRemaining <= 3 ? 'URGENT: ' : '';

        return (new MailMessage)
            ->subject("{$urgency}Échéance déclaration TVA - {$this->declaration->period_name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre déclaration TVA pour {$this->declaration->period_name} arrive à échéance.")
            ->line("**Date limite:** {$this->declaration->deadline->format('d/m/Y')}")
            ->line("**Jours restants:** {$this->daysRemaining}")
            ->line("**Montant TVA:** " . number_format($this->declaration->vat_to_pay, 2) . "€")
            ->line("**Communication:** {$this->declaration->payment_reference}")
            ->action('Voir la déclaration', url("/belgium/vat/{$this->declaration->id}"))
            ->line('Merci d\'utiliser ComptaPro Belgium!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'vat_deadline',
            'declaration_id' => $this->declaration->id,
            'period' => $this->declaration->period_name,
            'deadline' => $this->declaration->deadline->format('Y-m-d'),
            'days_remaining' => $this->daysRemaining,
            'amount' => $this->declaration->vat_to_pay,
            'payment_reference' => $this->declaration->payment_reference,
            'url' => url("/belgium/vat/{$this->declaration->id}"),
        ];
    }
}
