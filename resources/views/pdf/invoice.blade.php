<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $typeLabel }} {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        .header {
            margin-bottom: 40px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }
        .company-info {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .invoice-info {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            text-align: right;
        }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .invoice-title {
            font-size: 20pt;
            font-weight: bold;
            color: #007bff;
        }
        .invoice-number {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .addresses {
            margin: 30px 0;
        }
        .address-block {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .address-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 10px;
            color: #007bff;
        }
        .meta-info {
            margin: 30px 0;
            padding: 15px;
            background: #e9ecef;
            border-radius: 5px;
        }
        .meta-info table {
            width: 100%;
        }
        .meta-info td {
            padding: 5px;
        }
        .meta-info .label {
            font-weight: bold;
            width: 30%;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        table.items thead {
            background: #007bff;
            color: white;
        }
        table.items th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }
        table.items td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        table.items tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        table.items .text-right {
            text-align: right;
        }
        table.items .text-center {
            text-align: center;
        }
        .totals {
            margin: 20px 0;
            width: 40%;
            float: right;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        .totals .label {
            font-weight: bold;
        }
        .totals .amount {
            text-align: right;
        }
        .totals .total-row {
            background: #007bff;
            color: white;
            font-size: 12pt;
            font-weight: bold;
        }
        .notes {
            clear: both;
            margin: 40px 0;
            padding: 15px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .notes-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 9pt;
            color: #6c757d;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 9pt;
            font-weight: bold;
            margin-left: 10px;
        }
        .status-draft { background: #6c757d; color: white; }
        .status-sent { background: #17a2b8; color: white; }
        .status-paid { background: #28a745; color: white; }
        .status-overdue { background: #dc3545; color: white; }
        .text-muted {
            color: #6c757d;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <!-- Header -->
    @if($showHeader)
    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ $company->name }}</div>
            <div>{{ $company->address }}</div>
            <div>{{ $company->postal_code }} {{ $company->city }}</div>
            <div>{{ $company->country->name ?? '' }}</div>
            @if($company->vat_number)
            <div class="text-muted">TVA: {{ $company->vat_number }}</div>
            @endif
            @if($company->email)
            <div class="text-muted">Email: {{ $company->email }}</div>
            @endif
            @if($company->phone)
            <div class="text-muted">Tél: {{ $company->phone }}</div>
            @endif
        </div>
        <div class="invoice-info">
            <div class="invoice-title">{{ $typeLabel }}</div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <span class="status-badge status-{{ $invoice->status }}">
                {{ strtoupper($invoice->status) }}
            </span>
        </div>
    </div>
    @endif

    <!-- Addresses -->
    <div class="addresses">
        <div class="address-block">
            <div class="address-title">Client</div>
            <div><strong>{{ $customer->name }}</strong></div>
            @if($customer->address)
            <div>{{ $customer->address }}</div>
            @endif
            @if($customer->postal_code || $customer->city)
            <div>{{ $customer->postal_code }} {{ $customer->city }}</div>
            @endif
            @if($customer->country_code)
            <div>{{ $customer->country_code }}</div>
            @endif
            @if($customer->vat_number)
            <div class="text-muted">TVA: {{ $customer->vat_number }}</div>
            @endif
        </div>
        <div class="address-block" style="float: right;">
            <div class="address-title">Informations</div>
            <div><strong>Date:</strong> {{ \App\Services\PdfGenerator::formatDate($invoice->invoice_date) }}</div>
            @if($invoice->due_date)
            <div><strong>Échéance:</strong> {{ \App\Services\PdfGenerator::formatDate($invoice->due_date) }}</div>
            @endif
            @if($invoice->type === 'invoice')
            <div><strong>Conditions:</strong> {{ ucfirst(str_replace('_', ' ', $customer->payment_term ?? '30_days')) }}</div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table class="items">
        <thead>
            <tr>
                <th style="width: 45%;">Description</th>
                <th class="text-center" style="width: 10%;">Qté</th>
                <th class="text-right" style="width: 12%;">P.U. HT</th>
                <th class="text-center" style="width: 8%;">TVA</th>
                <th class="text-right" style="width: 12%;">Total HT</th>
                <th class="text-right" style="width: 13%;">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
            <tr>
                <td>
                    <strong>{{ $line->description }}</strong>
                    @if($line->product)
                    <br><span class="text-muted">Réf: {{ $line->product->sku }}</span>
                    @endif
                </td>
                <td class="text-center">{{ number_format($line->quantity, 2, ',', ' ') }}</td>
                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($line->unit_price, $company->country->currency ?? 'EUR') }}</td>
                <td class="text-center">{{ number_format($line->vat_rate, 1) }}%</td>
                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($line->subtotal, $company->country->currency ?? 'EUR') }}</td>
                <td class="text-right">{{ \App\Services\PdfGenerator::formatCurrency($line->total, $company->country->currency ?? 'EUR') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <table>
            <tr>
                <td class="label">Total HT</td>
                <td class="amount">{{ \App\Services\PdfGenerator::formatCurrency($invoice->subtotal, $company->country->currency ?? 'EUR') }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
            <tr>
                <td class="label">Remise</td>
                <td class="amount">- {{ \App\Services\PdfGenerator::formatCurrency($invoice->discount_amount, $company->country->currency ?? 'EUR') }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">TVA</td>
                <td class="amount">{{ \App\Services\PdfGenerator::formatCurrency($invoice->tax_amount, $company->country->currency ?? 'EUR') }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">TOTAL TTC</td>
                <td class="amount">{{ \App\Services\PdfGenerator::formatCurrency($invoice->total, $company->country->currency ?? 'EUR') }}</td>
            </tr>
            @if($invoice->paid_amount > 0)
            <tr>
                <td class="label">Déjà payé</td>
                <td class="amount">{{ \App\Services\PdfGenerator::formatCurrency($invoice->paid_amount, $company->country->currency ?? 'EUR') }}</td>
            </tr>
            <tr style="background: #28a745; color: white; font-weight: bold;">
                <td class="label">Reste à payer</td>
                <td class="amount">{{ \App\Services\PdfGenerator::formatCurrency($invoice->total - $invoice->paid_amount, $company->country->currency ?? 'EUR') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Notes -->
    @if($invoice->notes)
    <div class="notes">
        <div class="notes-title">Notes</div>
        <div>{{ $invoice->notes }}</div>
    </div>
    @endif

    @if($invoice->terms)
    <div class="notes" style="background: #d1ecf1; border-left-color: #0c5460;">
        <div class="notes-title">Conditions de paiement</div>
        <div>{{ $invoice->terms }}</div>
    </div>
    @endif

    <!-- Footer -->
    @if($showFooter)
    <div class="footer">
        @if($customFooter)
            {!! $customFooter !!}
        @else
            <div>{{ $company->name }} - {{ $company->address }}, {{ $company->postal_code }} {{ $company->city }}</div>
            @if($company->registration_number)
            <div>{{ $company->registration_number }}</div>
            @endif
            @if($company->vat_number)
            <div>TVA: {{ $company->vat_number }}</div>
            @endif
            <div style="margin-top: 10px;">{{ $typeLabel }} généré{{ $invoice->type === 'invoice' ? 'e' : '' }} le {{ \App\Services\PdfGenerator::formatDate(now()) }}</div>
        @endif
    </div>
    @endif
</body>
</html>
