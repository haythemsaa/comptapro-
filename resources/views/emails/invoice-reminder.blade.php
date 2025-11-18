<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel - {{ $invoice->invoice_number }}</title>
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
            @if($reminderType === 'final_notice')
                background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            @elseif($reminderType === 'second_reminder')
                background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
            @else
                background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            @endif
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
            margin-bottom: 25px;
            line-height: 1.8;
        }
        .alert-box {
            @if($reminderType === 'final_notice')
                background-color: #f8d7da;
                border-left: 4px solid #dc3545;
                color: #721c24;
            @elseif($reminderType === 'second_reminder')
                background-color: #fff3cd;
                border-left: 4px solid #fd7e14;
                color: #856404;
            @else
                background-color: #fff3cd;
                border-left: 4px solid #ffc107;
                color: #856404;
            @endif
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .alert-box h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .alert-box p {
            font-size: 14px;
            margin: 5px 0;
        }
        .invoice-details {
            background-color: #f8f9fa;
            border: 2px solid #e0e0e0;
            padding: 20px;
            margin: 30px 0;
            border-radius: 6px;
        }
        .invoice-details h3 {
            font-size: 16px;
            color: #007bff;
            margin-bottom: 15px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
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
        .overdue-amount {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            margin: 10px -15px -15px -15px;
            border-radius: 0 0 4px 4px;
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 20px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            @if($reminderType === 'final_notice')
                background-color: #dc3545;
            @else
                background-color: #007bff;
            @endif
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .payment-info {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .payment-info h4 {
            color: #004085;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .payment-info p {
            color: #004085;
            font-size: 14px;
            margin: 8px 0;
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
                @if($reminderType === 'final_notice')
                    Mise en demeure de paiement
                @elseif($reminderType === 'second_reminder')
                    Rappel urgent de paiement
                @else
                    Rappel de paiement
                @endif
            </p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                @if($reminderType === 'final_notice')
                    Madame, Monsieur,
                @else
                    Bonjour {{ $customer->name }},
                @endif
            </div>

            <div class="message-content">
                @if($reminderType === 'final_notice')
                    <p style="font-weight: 600;">Cette lettre constitue une mise en demeure de paiement au sens de l'article 1344 du Code civil.</p>

                    <p>Nous constatons que malgré nos précédents rappels, la facture {{ $invoice->invoice_number }} d'un montant de
                    <strong>{{ \App\Services\PdfGenerator::formatCurrency($invoice->total - $invoice->paid_amount, $company->country->currency ?? 'EUR') }}</strong>
                    demeure impayée depuis <strong>{{ $daysOverdue }} jours</strong>.</p>

                    <p>Nous vous mettons en demeure de procéder au règlement de cette facture dans un délai de <strong>8 jours</strong> à compter de la réception de ce courrier.</p>

                    <p>À défaut de paiement dans ce délai, nous nous réservons le droit d'engager toutes les procédures légales appropriées pour recouvrer les sommes dues,
                    ainsi que les éventuels intérêts de retard et frais de recouvrement, sans autre avis de notre part.</p>
                @elseif($reminderType === 'second_reminder')
                    <p>Nous vous avons récemment contacté concernant le paiement de la facture {{ $invoice->invoice_number }}, qui est maintenant en retard de <strong>{{ $daysOverdue }} jours</strong>.</p>

                    <p>Nous n'avons toujours pas reçu votre paiement d'un montant de
                    <strong>{{ \App\Services\PdfGenerator::formatCurrency($invoice->total - $invoice->paid_amount, $company->country->currency ?? 'EUR') }}</strong>.</p>

                    <p>Nous comprenons que des situations imprévues peuvent survenir. Si vous rencontrez des difficultés de paiement,
                    nous vous invitons à nous contacter rapidement afin de trouver ensemble une solution.</p>

                    <p><strong>Nous vous demandons de régulariser cette situation dans les meilleurs délais.</strong></p>
                @else
                    <p>Nous vous contactons concernant la facture {{ $invoice->invoice_number }} d'un montant de
                    <strong>{{ \App\Services\PdfGenerator::formatCurrency($invoice->total - $invoice->paid_amount, $company->country->currency ?? 'EUR') }}</strong>.</p>

                    <p>Cette facture a dépassé sa date d'échéance de <strong>{{ $daysOverdue }} jours</strong>.</p>

                    <p>Si vous avez déjà effectué ce paiement, veuillez ignorer ce message et accepter nos excuses pour ce rappel.</p>

                    <p>Dans le cas contraire, nous vous serions reconnaissants de bien vouloir procéder au règlement dans les plus brefs délais.</p>
                @endif
            </div>

            <!-- Alert Box -->
            <div class="alert-box">
                <h3>
                    @if($reminderType === 'final_notice')
                        ⚠️ Mise en demeure
                    @elseif($reminderType === 'second_reminder')
                        ⚠️ Paiement urgent requis
                    @else
                        ⏰ Facture en retard
                    @endif
                </h3>
                <p>
                    <strong>Retard : </strong>{{ $daysOverdue }} jour{{ $daysOverdue > 1 ? 's' : '' }}<br>
                    <strong>Date d'échéance : </strong>{{ \App\Services\PdfGenerator::formatDate($invoice->due_date) }}
                </p>
            </div>

            <!-- Invoice Details -->
            <div class="invoice-details">
                <h3>Détails de la facture</h3>

                <div class="detail-row">
                    <span class="detail-label">Numéro de facture :</span>
                    <span class="detail-value">{{ $invoice->invoice_number }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Date de facture :</span>
                    <span class="detail-value">{{ \App\Services\PdfGenerator::formatDate($invoice->invoice_date) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Date d'échéance :</span>
                    <span class="detail-value">{{ \App\Services\PdfGenerator::formatDate($invoice->due_date) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Jours de retard :</span>
                    <span class="detail-value" style="color: #dc3545; font-weight: 600;">{{ $daysOverdue }} jour{{ $daysOverdue > 1 ? 's' : '' }}</span>
                </div>

                <div class="overdue-amount">
                    <span>Montant dû</span>
                    <span>{{ \App\Services\PdfGenerator::formatCurrency($invoice->total - $invoice->paid_amount, $company->country->currency ?? 'EUR') }}</span>
                </div>
            </div>

            <!-- Payment Link (if enabled) -->
            @if($invoice->payment_link_enabled && $invoice->payment_token)
            <div class="button-container">
                <a href="{{ route('payment.show', ['token' => $invoice->payment_token]) }}" class="btn">
                    Payer maintenant
                </a>
            </div>
            @endif

            <!-- Payment Information -->
            @if($company->iban)
            <div class="payment-info">
                <h4>Informations de paiement</h4>
                <p><strong>Bénéficiaire :</strong> {{ $company->name }}</p>
                <p><strong>IBAN :</strong> {{ $company->iban }}</p>
                @if($company->bic)
                <p><strong>BIC :</strong> {{ $company->bic }}</p>
                @endif
                <p><strong>Référence à mentionner :</strong> {{ $invoice->invoice_number }}</p>
                <p style="margin-top: 15px; font-size: 13px;"><em>Merci de bien vouloir indiquer la référence de facture lors de votre virement.</em></p>
            </div>
            @endif

            <div class="message-content">
                @if($reminderType === 'final_notice')
                    Nous restons à votre disposition pour toute question relative à cette mise en demeure.<br><br>
                    Cordialement,<br>
                @else
                    Pour toute question concernant cette facture, n'hésitez pas à nous contacter.<br><br>
                    Cordialement,<br>
                @endif
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
                @if($reminderType === 'final_notice')
                    Ce courrier a valeur de mise en demeure au sens légal du terme.
                @else
                    Si vous avez déjà effectué le paiement, veuillez ignorer ce rappel.
                @endif
            </div>
        </div>
    </div>
</body>
</html>
