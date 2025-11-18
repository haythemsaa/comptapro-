<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Compte de Résultat - {{ $company->name }}</title>
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
        .section-header { background: #343a40 !important; color: white !important; font-weight: bold; }
        .total-row { background: #28a745; color: white; font-weight: bold; font-size: 11pt; }
        .subtotal-row { background: #e9ecef; font-weight: bold; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #dee2e6; text-align: center; font-size: 9pt; color: #6c757d; }
        .positive { color: #28a745; font-weight: bold; }
        .negative { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    @if($showHeader ?? true)
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">Compte de Résultat (Profit & Loss)</div>
        <div class="report-subtitle">
            Période: {{ \App\Services\PdfGenerator::formatDate($fromDate) }} - {{ \App\Services\PdfGenerator::formatDate($toDate) }}
        </div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Code</th>
                <th style="width: 55%;">Compte</th>
                <th class="text-right" style="width: 30%;">Montant ({{ $company->country->currency ?? 'EUR' }})</th>
            </tr>
        </thead>
        <tbody>
            <!-- PRODUITS (REVENUE) -->
            <tr class="section-header">
                <td colspan="3">PRODUITS D'EXPLOITATION</td>
            </tr>
            @php
                $totalRevenue = 0;
            @endphp
            @foreach($revenueAccounts as $account)
                @php
                    $balance = $account->balance ?? 0;
                    $totalRevenue += $balance;
                @endphp
                <tr>
                    <td>{{ $account->code }}</td>
                    <td>{{ $account->name }}</td>
                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($balance, $company->country->currency ?? 'EUR') }}</td>
                </tr>
            @endforeach
            <tr class="subtotal-row">
                <td colspan="2">Total Produits</td>
                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalRevenue, $company->country->currency ?? 'EUR') }}</td>
            </tr>

            <!-- CHARGES (EXPENSES) -->
            <tr class="section-header">
                <td colspan="3">CHARGES D'EXPLOITATION</td>
            </tr>
            @php
                $totalExpense = 0;
            @endphp
            @foreach($expenseAccounts as $account)
                @php
                    $balance = $account->balance ?? 0;
                    $totalExpense += $balance;
                @endphp
                <tr>
                    <td>{{ $account->code }}</td>
                    <td>{{ $account->name }}</td>
                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($balance, $company->country->currency ?? 'EUR') }}</td>
                </tr>
            @endforeach
            <tr class="subtotal-row">
                <td colspan="2">Total Charges</td>
                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalExpense, $company->country->currency ?? 'EUR') }}</td>
            </tr>

            <!-- RÉSULTAT NET -->
            @php
                $netIncome = $totalRevenue - $totalExpense;
                $netIncomeClass = $netIncome >= 0 ? 'positive' : 'negative';
            @endphp
            <tr class="total-row">
                <td colspan="2">RÉSULTAT NET</td>
                <td class="text-right {{ $netIncomeClass }}">
                    {{ \App\Services\PdfGenerator::formatCurrency($netIncome, $company->country->currency ?? 'EUR') }}
                </td>
            </tr>
        </tbody>
    </table>

    @if($showFooter ?? true)
    <div class="footer">
        <div>{{ $company->name }} - Rapport généré le {{ \App\Services\PdfGenerator::formatDate(now()) }}</div>
    </div>
    @endif
</body>
</html>
