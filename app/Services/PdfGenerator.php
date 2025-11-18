<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Modules\Invoicing\Models\Invoice;
use App\Models\Modules\Core\Models\Company;

class PdfGenerator
{
    /**
     * Generate invoice PDF
     */
    public function generateInvoicePdf(Invoice $invoice, array $options = []): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['customer', 'company.country', 'lines.product', 'createdBy']);

        $typeLabels = [
            'quote' => 'Devis',
            'invoice' => 'Facture',
            'credit_note' => 'Avoir',
        ];

        $data = [
            'invoice' => $invoice,
            'company' => $invoice->company,
            'customer' => $invoice->customer,
            'lines' => $invoice->lines,
            'typeLabel' => $typeLabels[$invoice->type] ?? 'Facture',
            'showFooter' => $options['showFooter'] ?? true,
            'showHeader' => $options['showHeader'] ?? true,
            'customFooter' => $options['customFooter'] ?? null,
            'customHeader' => $options['customHeader'] ?? null,
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);

        // Configuration PDF
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return $pdf;
    }

    /**
     * Generate Profit & Loss PDF
     */
    public function generateProfitLossPdf(Company $company, $data, array $options = []): \Barryvdh\DomPDF\PDF
    {
        $pdfData = array_merge($data, [
            'company' => $company,
            'title' => 'Compte de Résultat',
            'showFooter' => $options['showFooter'] ?? true,
            'showHeader' => $options['showHeader'] ?? true,
        ]);

        $pdf = Pdf::loadView('pdf.reports.profit-loss', $pdfData);
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Generate Balance Sheet PDF
     */
    public function generateBalanceSheetPdf(Company $company, $data, array $options = []): \Barryvdh\DomPDF\PDF
    {
        $pdfData = array_merge($data, [
            'company' => $company,
            'title' => 'Bilan',
            'showFooter' => $options['showFooter'] ?? true,
            'showHeader' => $options['showHeader'] ?? true,
        ]);

        $pdf = Pdf::loadView('pdf.reports.balance-sheet', $pdfData);
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Generate VAT Report PDF
     */
    public function generateVatReportPdf(Company $company, $data, array $options = []): \Barryvdh\DomPDF\PDF
    {
        $pdfData = array_merge($data, [
            'company' => $company,
            'title' => 'Déclaration de TVA',
            'showFooter' => $options['showFooter'] ?? true,
            'showHeader' => $options['showHeader'] ?? true,
        ]);

        $pdf = Pdf::loadView('pdf.reports.vat-report', $pdfData);
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Generate Detailed Balance PDF (N vs N-1)
     */
    public function generateDetailedBalancePdf(Company $company, $data, array $options = []): \Barryvdh\DomPDF\PDF
    {
        $pdfData = array_merge($data, [
            'company' => $company,
            'title' => 'Balance Détaillée N vs N-1',
            'showFooter' => $options['showFooter'] ?? true,
            'showHeader' => $options['showHeader'] ?? true,
        ]);

        $pdf = Pdf::loadView('pdf.reports.detailed-balance', $pdfData);
        $pdf->setPaper('a4', 'landscape'); // Paysage pour plus de colonnes

        return $pdf;
    }

    /**
     * Helper: Format currency
     */
    public static function formatCurrency(float $amount, string $currency = 'EUR'): string
    {
        return number_format($amount, 2, ',', ' ') . ' ' . $currency;
    }

    /**
     * Helper: Format date
     */
    public static function formatDate($date): string
    {
        if (!$date) return '-';
        return \Carbon\Carbon::parse($date)->format('d/m/Y');
    }
}
