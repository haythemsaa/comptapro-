<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\PdfGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Invoice $invoice,
        public string $messageBody = '',
        public bool $attachPdf = true
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $typeLabels = [
            'quote' => 'Devis',
            'invoice' => 'Facture',
            'credit_note' => 'Avoir',
        ];

        $typeLabel = $typeLabels[$this->invoice->type] ?? 'Facture';

        return new Envelope(
            from: new Address(
                $this->invoice->company->email ?? config('mail.from.address'),
                $this->invoice->company->name
            ),
            replyTo: [
                new Address(
                    $this->invoice->company->email ?? config('mail.from.address'),
                    $this->invoice->company->name
                ),
            ],
            subject: "{$typeLabel} {$this->invoice->invoice_number} - {$this->invoice->company->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'company' => $this->invoice->company,
                'customer' => $this->invoice->customer,
                'messageBody' => $this->messageBody,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (!$this->attachPdf) {
            return [];
        }

        $typeLabels = [
            'quote' => 'Devis',
            'invoice' => 'Facture',
            'credit_note' => 'Avoir',
        ];

        $typeLabel = $typeLabels[$this->invoice->type] ?? 'Facture';

        $pdfGenerator = new PdfGenerator();
        $pdf = $pdfGenerator->generateInvoicePdf($this->invoice);

        $filename = str_replace(' ', '_', "{$typeLabel}_{$this->invoice->invoice_number}.pdf");

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
