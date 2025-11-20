<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkflowCompleted extends Notification
{
    use Queueable;

    private string $workflowType;
    private array $results;

    public function __construct(string $workflowType, array $results)
    {
        $this->workflowType = $workflowType;
        $this->results = $results;
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
            ->subject("✅ Workflow {$this->workflowType} terminé")
            ->greeting('Bonjour,')
            ->line("Le workflow automatique a été exécuté avec succès:");

        // Ajouter les statistiques
        if (isset($this->results['steps']['process_documents']['data'])) {
            $docs = $this->results['steps']['process_documents']['data'];
            $message->line("📄 Documents traités: {$docs['invoices_processed']} factures, {$docs['expenses_processed']} dépenses");
        }

        if (isset($this->results['steps']['financial_reports']['data'])) {
            $message->line("📊 États financiers générés avec succès");
        }

        if (isset($this->results['steps']['tax_declarations']['data'])) {
            $message->line("📋 Déclarations fiscales générées");
        }

        $duration = $this->results['duration_seconds'] ?? 0;
        $message->line("⏱️ Durée: {$duration} secondes");

        return $message
            ->action('Voir le détail', url('/automation/dashboard'))
            ->line('Merci d\'utiliser ComptaPro Tunisia!');
    }

    /**
     * Représentation tableau
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'workflow_completed',
            'workflow_type' => $this->workflowType,
            'results' => $this->results,
        ];
    }
}
