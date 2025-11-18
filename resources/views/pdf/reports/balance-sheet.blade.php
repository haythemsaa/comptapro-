<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bilan - {{ $company->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #333; }
        .header { margin-bottom: 30px; border-bottom: 3px solid #007bff; padding-bottom: 20px; }
        .company-name { font-size: 18pt; font-weight: bold; color: #007bff; }
        .report-title { font-size: 16pt; font-weight: bold; margin: 10px 0; }
        .report-subtitle { color: #6c757d; font-size: 10pt; }

        .balance-sheet-container { margin: 20px 0; }
        .side-by-side { display: table; width: 100%; }
        .column { display: table-cell; width: 50%; vertical-align: top; padding: 0 5px; }

        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        thead { background: #007bff; color: white; }
        th { padding: 10px 8px; text-align: left; font-weight: bold; font-size: 9pt; }
        td { padding: 8px 8px; border-bottom: 1px solid #dee2e6; font-size: 9pt; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .section-header { background: #343a40 !important; color: white !important; font-weight: bold; }
        .subsection-header { background: #6c757d !important; color: white !important; font-size: 9pt; }
        .total-row { background: #28a745; color: white; font-weight: bold; font-size: 10pt; }
        .subtotal-row { background: #e9ecef; font-weight: bold; font-size: 9pt; }

        .footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #dee2e6; text-align: center; font-size: 9pt; color: #6c757d; }

        .summary-box {
            background: #e7f3ff;
            border: 2px solid #007bff;
            padding: 15px;
            margin: 30px 0;
            border-radius: 5px;
            text-align: center;
        }
        .summary-box h3 { color: #007bff; margin-bottom: 10px; font-size: 12pt; }
        .summary-box .amount { font-size: 16pt; font-weight: bold; color: #007bff; }

        .indent-1 { padding-left: 15px; }
        .indent-2 { padding-left: 30px; }
    </style>
</head>
<body>
    @if($showHeader ?? true)
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">Bilan (Balance Sheet)</div>
        <div class="report-subtitle">
            Au {{ \App\Services\PdfGenerator::formatDate($asOfDate) }}
        </div>
    </div>
    @endif

    <div class="balance-sheet-container">
        <div class="side-by-side">
            <!-- ACTIF (ASSETS) - Left Column -->
            <div class="column">
                <table>
                    <thead>
                        <tr>
                            <th colspan="3" class="section-header">ACTIF (Assets)</th>
                        </tr>
                        <tr>
                            <th style="width: 15%;">Code</th>
                            <th style="width: 55%;">Compte</th>
                            <th class="text-right" style="width: 30%;">Montant ({{ $company->country->currency ?? 'EUR' }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Group assets by category
                            $currentAssets = $assetAccounts->filter(fn($a) => str_starts_with($a->code, '5') || str_starts_with($a->code, '4'));
                            $fixedAssets = $assetAccounts->filter(fn($a) => str_starts_with($a->code, '2'));
                            $otherAssets = $assetAccounts->reject(fn($a) =>
                                str_starts_with($a->code, '5') || str_starts_with($a->code, '4') || str_starts_with($a->code, '2')
                            );

                            $totalCurrentAssets = 0;
                            $totalFixedAssets = 0;
                            $totalOtherAssets = 0;
                        @endphp

                        @if($fixedAssets->count() > 0)
                            <tr class="subsection-header">
                                <td colspan="3">Actif immobilisé (Fixed Assets)</td>
                            </tr>
                            @foreach($fixedAssets as $account)
                                @php
                                    $balance = $account->balance ?? 0;
                                    $totalFixedAssets += $balance;
                                @endphp
                                <tr>
                                    <td>{{ $account->code }}</td>
                                    <td class="indent-1">{{ $account->name }}</td>
                                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($balance, $company->country->currency ?? 'EUR') }}</td>
                                </tr>
                            @endforeach
                            <tr class="subtotal-row">
                                <td colspan="2">Sous-total actif immobilisé</td>
                                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalFixedAssets, $company->country->currency ?? 'EUR') }}</td>
                            </tr>
                        @endif

                        @if($currentAssets->count() > 0)
                            <tr class="subsection-header">
                                <td colspan="3">Actif circulant (Current Assets)</td>
                            </tr>
                            @foreach($currentAssets as $account)
                                @php
                                    $balance = $account->balance ?? 0;
                                    $totalCurrentAssets += $balance;
                                @endphp
                                <tr>
                                    <td>{{ $account->code }}</td>
                                    <td class="indent-1">{{ $account->name }}</td>
                                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($balance, $company->country->currency ?? 'EUR') }}</td>
                                </tr>
                            @endforeach
                            <tr class="subtotal-row">
                                <td colspan="2">Sous-total actif circulant</td>
                                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalCurrentAssets, $company->country->currency ?? 'EUR') }}</td>
                            </tr>
                        @endif

                        @if($otherAssets->count() > 0)
                            @foreach($otherAssets as $account)
                                @php
                                    $balance = $account->balance ?? 0;
                                    $totalOtherAssets += $balance;
                                @endphp
                                <tr>
                                    <td>{{ $account->code }}</td>
                                    <td>{{ $account->name }}</td>
                                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($balance, $company->country->currency ?? 'EUR') }}</td>
                                </tr>
                            @endforeach
                        @endif

                        <tr class="total-row">
                            <td colspan="2">TOTAL ACTIF</td>
                            <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalAssets, $company->country->currency ?? 'EUR') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PASSIF (LIABILITIES & EQUITY) - Right Column -->
            <div class="column">
                <table>
                    <thead>
                        <tr>
                            <th colspan="3" class="section-header">PASSIF (Liabilities & Equity)</th>
                        </tr>
                        <tr>
                            <th style="width: 15%;">Code</th>
                            <th style="width: 55%;">Compte</th>
                            <th class="text-right" style="width: 30%;">Montant ({{ $company->country->currency ?? 'EUR' }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $currentLiabilities = $liabilityAccounts->filter(fn($a) => str_starts_with($a->code, '4'));
                            $longTermLiabilities = $liabilityAccounts->filter(fn($a) => str_starts_with($a->code, '1') || str_starts_with($a->code, '16'));
                            $otherLiabilities = $liabilityAccounts->reject(fn($a) =>
                                str_starts_with($a->code, '4') || str_starts_with($a->code, '1') || str_starts_with($a->code, '16')
                            );

                            $totalCurrentLiabilities = 0;
                            $totalLongTermLiabilities = 0;
                            $totalOtherLiabilities = 0;
                        @endphp

                        <!-- CAPITAUX PROPRES (EQUITY) -->
                        @if($equityAccounts->count() > 0)
                            <tr class="subsection-header">
                                <td colspan="3">Capitaux propres (Equity)</td>
                            </tr>
                            @foreach($equityAccounts as $account)
                                @php
                                    $balance = $account->balance ?? 0;
                                @endphp
                                <tr>
                                    <td>{{ $account->code }}</td>
                                    <td class="indent-1">{{ $account->name }}</td>
                                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($balance, $company->country->currency ?? 'EUR') }}</td>
                                </tr>
                            @endforeach
                            <tr class="subtotal-row">
                                <td colspan="2">Sous-total capitaux propres</td>
                                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalEquity, $company->country->currency ?? 'EUR') }}</td>
                            </tr>
                        @endif

                        <!-- DETTES À LONG TERME (LONG TERM LIABILITIES) -->
                        @if($longTermLiabilities->count() > 0)
                            <tr class="subsection-header">
                                <td colspan="3">Dettes à long terme (Long-term liabilities)</td>
                            </tr>
                            @foreach($longTermLiabilities as $account)
                                @php
                                    $balance = $account->balance ?? 0;
                                    $totalLongTermLiabilities += $balance;
                                @endphp
                                <tr>
                                    <td>{{ $account->code }}</td>
                                    <td class="indent-1">{{ $account->name }}</td>
                                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($balance, $company->country->currency ?? 'EUR') }}</td>
                                </tr>
                            @endforeach
                            <tr class="subtotal-row">
                                <td colspan="2">Sous-total dettes LT</td>
                                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalLongTermLiabilities, $company->country->currency ?? 'EUR') }}</td>
                            </tr>
                        @endif

                        <!-- DETTES À COURT TERME (CURRENT LIABILITIES) -->
                        @if($currentLiabilities->count() > 0)
                            <tr class="subsection-header">
                                <td colspan="3">Dettes à court terme (Current liabilities)</td>
                            </tr>
                            @foreach($currentLiabilities as $account)
                                @php
                                    $balance = $account->balance ?? 0;
                                    $totalCurrentLiabilities += $balance;
                                @endphp
                                <tr>
                                    <td>{{ $account->code }}</td>
                                    <td class="indent-1">{{ $account->name }}</td>
                                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($balance, $company->country->currency ?? 'EUR') }}</td>
                                </tr>
                            @endforeach
                            <tr class="subtotal-row">
                                <td colspan="2">Sous-total dettes CT</td>
                                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalCurrentLiabilities, $company->country->currency ?? 'EUR') }}</td>
                            </tr>
                        @endif

                        @if($otherLiabilities->count() > 0)
                            @foreach($otherLiabilities as $account)
                                @php
                                    $balance = $account->balance ?? 0;
                                    $totalOtherLiabilities += $balance;
                                @endphp
                                <tr>
                                    <td>{{ $account->code }}</td>
                                    <td>{{ $account->name }}</td>
                                    <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($balance, $company->country->currency ?? 'EUR') }}</td>
                                </tr>
                            @endforeach
                        @endif

                        <tr class="total-row">
                            <td colspan="2">TOTAL PASSIF</td>
                            <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($totalLiabilities + $totalEquity, $company->country->currency ?? 'EUR') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Box -->
    @php
        $balanceDifference = $totalAssets - ($totalLiabilities + $totalEquity);
        $isBalanced = abs($balanceDifference) < 0.01;
    @endphp
    <div class="summary-box" style="@if(!$isBalanced) background: #fff3cd; border-color: #ffc107; @endif">
        <h3>@if($isBalanced) ✓ Bilan équilibré @else ⚠ Attention : Bilan non équilibré @endif</h3>
        @if(!$isBalanced)
            <p style="color: #856404; font-size: 10pt;">
                Différence : {{ \App\Services\PdfGenerator::formatCurrency($balanceDifference, $company->country->currency ?? 'EUR') }}
            </p>
        @else
            <p style="color: #155724; font-size: 10pt;">
                Total Actif = Total Passif = <span class="amount">{{ \App\Services\PdfGenerator::formatCurrency($totalAssets, $company->country->currency ?? 'EUR') }}</span>
            </p>
        @endif
    </div>

    @if($showFooter ?? true)
    <div class="footer">
        <div>{{ $company->name }} - Bilan généré le {{ \App\Services\PdfGenerator::formatDate(now()) }}</div>
        <div style="margin-top: 5px;">Ce document est généré automatiquement et n'a de valeur qu'à titre informatif.</div>
    </div>
    @endif
</body>
</html>
