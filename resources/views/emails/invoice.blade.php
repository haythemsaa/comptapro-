<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .email-header p {
            font-size: 16px;
            opacity: 0.95;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }
        .message-content {
            font-size: 15px;
            color: #555;
            margin-bottom: 30px;
            white-space: pre-line;
        }
        .invoice-details {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }
        .invoice-details h3 {
            font-size: 16px;
            color: #007bff;
            margin-bottom: 15px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .total-row {
            background-color: #007bff;
            color: white;
            padding: 15px;
            margin: 10px -15px -15px -15px;
            border-radius: 0 0 4px 4px;
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 18px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-draft {
            background-color: #6c757d;
            color: white;
        }
        .status-sent {
            background-color: #17a2b8;
            color: white;
        }
        .status-paid {
            background-color: #28a745;
            color: white;
        }
        .status-overdue {
            background-color: #dc3545;
            color: white;
        }
        .payment-info {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .payment-info h4 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .payment-info p {
            color: #856404;
            font-size: 14px;
            margin: 5px 0;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .company-info {
            font-size: 14px;
            color: #666;
            line-height: 1.8;
        }
        .footer-note {
            font-size: 12px;
            color: #999;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }
            .email-header {
                padding: 30px 20px;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-value {
                margin-top: 5px;
                font-weight: 600;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>{{ $company->name }}</h1>
            <p>
                @if($invoice->type === 'quote')
                    Nouveau devis
                @elseif($invoice->type === 'invoice')
                    Nouvelle facture
                @else
                    Nouvel avoir
                @endif
            </p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Bonjour {{ $customer->name }},
            </div>

            @if($messageBody)
                <div class="message-content">{{ $messageBody }}</div>
            @else
                <div class="message-content">
                    @if($invoice->type === 'quote')
                        Veuillez trouver ci-joint le devis {{ $invoice->invoice_number }}.

                        Nous restons à votre disposition pour toute question concernant cette proposition.
                    @elseif($invoice->type === 'invoice')
                        Veuillez trouver ci-joint la facture {{ $invoice->invoice_number }}.

                        Nous vous remercions de votre confiance et restons à votre disposition pour toute question.
                    @else
                        Veuillez trouver ci-joint l'avoir {{ $invoice->invoice_number }}.

                        Ce document annule et remplace les éléments concernés de la facture initiale.
                    @endif
                </div>
            @endif

            <!-- Invoice Details -->
            <div class="invoice-details">
                <h3>
                    @if($invoice->type === 'quote')
                        Détails du devis
                    @elseif($invoice->type === 'invoice')
                        Détails de la facture
                    @else
                        Détails de l'avoir
                    @endif
                </h3>

                <div class="detail-row">
                    <span class="detail-label">Numéro :</span>
                    <span class="detail-value">{{ $invoice->invoice_number }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Date :</span>
                    <span class="detail-value">{{ \App\Services\PdfGenerator::formatDate($invoice->invoice_date) }}</span>
                </div>

                @if($invoice->due_date && $invoice->type === 'invoice')
                <div class="detail-row">
                    <span class="detail-label">Date d'échéance :</span>
                    <span class="detail-value">{{ \App\Services\PdfGenerator::formatDate($invoice->due_date) }}</span>
                </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label">Statut :</span>
                    <span class="detail-value">
                        <span class="status-badge status-{{ $invoice->status }}">
                            {{ strtoupper($invoice->status) }}
                        </span>
                    </span>
                </div>

                <div class="total-row">
                    <span>Total TTC</span>
                    <span>{{ \App\Services\PdfGenerator::formatCurrency($invoice->total, $company->country->currency ?? 'EUR') }}</span>
                </div>
            </div>

            <!-- Payment Link (if enabled) -->
            @if($invoice->payment_link_enabled && $invoice->payment_token)
            <div class="button-container">
                <a href="{{ route('payment.show', ['token' => $invoice->payment_token]) }}" class="btn">
                    Payer en ligne
                </a>
            </div>
            @endif

            <!-- Payment Information -->
            @if($invoice->type === 'invoice' && $company->iban)
            <div class="payment-info">
                <h4>Informations de paiement</h4>
                <p><strong>IBAN :</strong> {{ $company->iban }}</p>
                @if($company->bic)
                <p><strong>BIC :</strong> {{ $company->bic }}</p>
                @endif
                <p><strong>Référence à mentionner :</strong> {{ $invoice->invoice_number }}</p>
            </div>
            @endif

            <div class="message-content">
                Cordialement,<br>
                <strong>{{ $company->name }}</strong>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="company-info">
                <strong>{{ $company->name }}</strong><br>
                @if($company->address)
                    {{ $company->address }}<br>
                @endif
                @if($company->postal_code || $company->city)
                    {{ $company->postal_code }} {{ $company->city }}<br>
                @endif
                @if($company->phone)
                    Tél : {{ $company->phone }}<br>
                @endif
                @if($company->email)
                    Email : {{ $company->email }}<br>
                @endif
                @if($company->vat_number)
                    TVA : {{ $company->vat_number }}
                @endif
            </div>

            <div class="footer-note">
                Cet email a été envoyé automatiquement par {{ $company->name }}.<br>
                Veuillez ne pas répondre directement à cet email. Pour toute question, contactez-nous à {{ $company->email ?? config('mail.from.address') }}.
            </div>
        </div>
    </div>
</body>
</html>
