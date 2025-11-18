<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Déclaration de TVA - {{ $company->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #333; }
        .header { margin-bottom: 30px; border-bottom: 3px solid #007bff; padding-bottom: 20px; }
        .company-name { font-size: 18pt; font-weight: bold; color: #007bff; }
        .report-title { font-size: 16pt; font-weight: bold; margin: 10px 0; }
        .report-subtitle { color: #6c757d; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        thead { background: #007bff; color: white; }
        th { padding: 12px 8px; text-align: left; font-weight: bold; }
        td { padding: 10px 8px; border-bottom: 1px solid #dee2e6; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background: #28a745; color: white; font-weight: bold; font-size: 11pt; }
        .section-header { background: #343a40 !important; color: white !important; font-weight: bold; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #dee2e6; text-align: center; font-size: 9pt; color: #6c757d; }
    </style>
</head>
<body>
    @if($showHeader ?? true)
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">Déclaration de TVA</div>
        <div class="report-subtitle">
            Période: {{ \App\Services\PdfGenerator::formatDate($fromDate) }} - {{ \App\Services\PdfGenerator::formatDate($toDate) }}
        </div>
    </div>
    @endif

    <!-- TVA COLLECTÉE -->
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Taux TVA</th>
                <th class="text-right" style="width: 25%;">Base HT</th>
                <th class="text-right" style="width: 25%;">TVA Collectée</th>
                <th class="text-right" style="width: 30%;">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-header">
                <td colspan="4">TVA COLLECTÉE (Ventes)</td>
            </tr>
            @php
                $totalBaseHT = 0;
                $totalVATCollected = 0;
                $totalTTC = 0;
            @endphp
            @foreach($vatByRate as $rate => $data)
                @php
                    $totalBaseHT += $data['base_ht'];
                    $totalVATCollected += $data['vat'];
                    $totalTTC += $data['total_ttc'];
                @endphp
                <tr>
                    <td>{{ number_format($rate, 1) }}%</td>
                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($data['base_ht'], $company->country->currency ?? 'EUR') }}</td>
                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($data['vat'], $company->country->currency ?? 'EUR') }}</td>
                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($data['total_ttc'], $company->country->currency ?? 'EUR') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalBaseHT, $company->country->currency ?? 'EUR') }}</td>
                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalVATCollected, $company->country->currency ?? 'EUR') }}</td>
                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalTTC, $company->country->currency ?? 'EUR') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- RÉCAPITULATIF -->
    <table>
        <thead>
            <tr>
                <th style="width: 70%;">Description</th>
                <th class="text-right" style="width: 30%;">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-header">
                <td colspan="2">RÉCAPITULATIF TVA</td>
            </tr>
            <tr>
                <td>TVA Collectée (sur ventes)</td>
                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalVATCollected, $company->country->currency ?? 'EUR') }}</td>
            </tr>
            <tr>
                <td>TVA Déductible (sur achats)</td>
                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($vatDeductible ?? 0, $company->country->currency ?? 'EUR') }}</td>
            </tr>
            <tr class="total-row">
                <td>TVA À PAYER</td>
                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalVATCollected - ($vatDeductible ?? 0), $company->country->currency ?? 'EUR') }}</td>
            </tr>
        </tbody>
    </table>

    @if($showFooter ?? true)
    <div class="footer">
        <div>{{ $company->name }} - Rapport généré le {{ \App\Services\PdfGenerator::formatDate(now()) }}</div>
        <div style="margin-top: 10px; font-style: italic;">Note: TVA déductible en attente du module Achats complet</div>
    </div>
    @endif
</body>
</html>
