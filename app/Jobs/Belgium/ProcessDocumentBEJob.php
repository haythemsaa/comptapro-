<?php

namespace App\Jobs\Belgium;

use App\Models\Company;
use App\Services\Belgium\AutoAccountingServiceBE;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Job pour traiter un document en arrière-plan
 */
class ProcessDocumentBEJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Company $company,
        public string $documentPath,
        public string $documentType,
        public ?int $userId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AutoAccountingServiceBE $service): void
    {
        Log::info("Processing document BE: {$this->documentPath} for company {$this->company->id}");

        try {
            $result = $service->processDocument(
                $this->company,
                $this->documentPath,
                $this->documentType
            );

            if ($result['success']) {
                Log::info("Document processed successfully", [
                    'company_id' => $this->company->id,
                    'confidence' => $result['confidence'],
                    'auto_validated' => $result['auto_validated'],
                ]);

                // Notification utilisateur
                if ($this->userId) {
                    // TODO: Envoyer notification à l'utilisateur
                }
            } else {
                Log::error("Document processing failed", [
                    'company_id' => $this->company->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                $this->fail(new \Exception($result['error'] ?? 'Processing failed'));
            }
        } catch (\Exception $e) {
            Log::error("Exception processing document BE", [
                'company_id' => $this->company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job failed after {$this->tries} attempts", [
            'company_id' => $this->company->id,
            'document_path' => $this->documentPath,
            'error' => $exception->getMessage(),
        ]);

        // TODO: Notifier l'utilisateur de l'échec
    }
}
