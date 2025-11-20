<?php

namespace App\Services\Belgium;

use App\Models\Belgium\PayrollBE;
use App\Models\Belgium\VATDeclarationBE;
use App\Models\Belgium\CompanyTaxBE;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

/**
 * Service de génération de PDF pour la Belgique
 * - Fiches de paie
 * - Déclarations TVA
 * - Déclarations IS
 */
class PDFGeneratorBE
{
    /**
     * Générer une fiche de paie PDF
     */
    public function generatePayslipPDF(PayrollBE $payroll): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'payroll' => $payroll,
            'employee' => $payroll->employee,
            'company' => $payroll->company,
            'period' => "{$payroll->month}/{$payroll->year}",
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        return Pdf::loadView('belgium.pdf.payslip', $data)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 15)
            ->setOption('margin-right', 15);
    }

    /**
     * Générer une déclaration TVA PDF
     */
    public function generateVATDeclarationPDF(VATDeclarationBE $declaration): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'declaration' => $declaration,
            'company' => $declaration->company,
            'period' => $declaration->period_name,
            'generated_at' => now()->format('d/m/Y H:i'),
            'grids' => $this->prepareVATGrids($declaration),
        ];

        return Pdf::loadView('belgium.pdf.vat-declaration', $data)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10);
    }

    /**
     * Générer une déclaration IS PDF
     */
    public function generateCompanyTaxPDF(CompanyTaxBE $tax): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'tax' => $tax,
            'company' => $tax->company,
            'fiscal_year' => $tax->fiscal_year,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        return Pdf::loadView('belgium.pdf.company-tax', $data)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10);
    }

    /**
     * Préparer les grilles TVA pour l'affichage
     */
    protected function prepareVATGrids(VATDeclarationBE $declaration): array
    {
        return [
            // Grilles Ventes
            ['code' => '01', 'label' => 'Opérations à 21%', 'amount' => $declaration->sales_21],
            ['code' => '02', 'label' => 'TVA 21%', 'amount' => $declaration->vat_21],
            ['code' => '03', 'label' => 'Opérations à 12%', 'amount' => $declaration->sales_12],
            ['code' => '04', 'label' => 'TVA 12%', 'amount' => $declaration->vat_12],
            ['code' => '05', 'label' => 'Opérations à 6%', 'amount' => $declaration->sales_6],
            ['code' => '06', 'label' => 'TVA 6%', 'amount' => $declaration->vat_6],
            ['code' => '44', 'label' => 'Opérations à 0%', 'amount' => $declaration->sales_0],
            ['code' => '46', 'label' => 'Exportations hors UE', 'amount' => $declaration->sales_export],
            ['code' => '47', 'label' => 'Livraisons intracommunautaires', 'amount' => $declaration->sales_intracommunity],

            // Grilles Achats
            ['code' => '81', 'label' => 'Achats Belgique', 'amount' => $declaration->purchases_domestic],
            ['code' => '59', 'label' => 'TVA déductible', 'amount' => $declaration->vat_deductible],
            ['code' => '86', 'label' => 'Achats intracommunautaires', 'amount' => $declaration->purchases_intracommunity],
            ['code' => '88', 'label' => 'TVA intracommunautaire', 'amount' => $declaration->vat_intracommunity],

            // Totaux
            ['code' => 'XX', 'label' => 'TVA collectée', 'amount' => $declaration->vat_collected, 'bold' => true],
            ['code' => 'YY', 'label' => 'TVA déductible', 'amount' => $declaration->vat_deductible, 'bold' => true],
            ['code' => 'ZZ', 'label' => 'TVA à payer', 'amount' => $declaration->vat_to_pay, 'bold' => true, 'highlight' => true],
        ];
    }

    /**
     * Télécharger le PDF
     */
    public function downloadPayslip(PayrollBE $payroll, string $filename = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $filename ?? "fiche-paie-{$payroll->employee->full_name}-{$payroll->month}-{$payroll->year}.pdf";
        $pdf = $this->generatePayslipPDF($payroll);

        return $pdf->download($filename);
    }

    /**
     * Stream le PDF dans le navigateur
     */
    public function streamPayslip(PayrollBE $payroll): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $pdf = $this->generatePayslipPDF($payroll);
        return $pdf->stream();
    }
}
