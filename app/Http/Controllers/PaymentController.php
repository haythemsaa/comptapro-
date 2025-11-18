<?php

namespace App\Http\Controllers;

use App\Models\Modules\Invoicing\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Show public payment page
     */
    public function show(string $token): Response
    {
        $invoice = Invoice::where('payment_token', $token)
            ->where('payment_link_enabled', true)
            ->with(['customer', 'company.country', 'lines'])
            ->firstOrFail();

        // Check if link has expired
        if ($invoice->payment_link_expires_at && $invoice->payment_link_expires_at->isPast()) {
            abort(410, 'Ce lien de paiement a expiré.');
        }

        // Check if already paid
        if ($invoice->status === 'paid') {
            return Inertia::render('Payment/AlreadyPaid', [
                'invoice' => $invoice,
            ]);
        }

        return Inertia::render('Payment/Show', [
            'invoice' => $invoice,
            'remainingAmount' => $invoice->total - $invoice->paid_amount,
        ]);
    }

    /**
     * Process payment (Stripe integration point)
     */
    public function process(Request $request, string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->where('payment_link_enabled', true)
            ->firstOrFail();

        $validated = $request->validate([
            'payment_method' => 'required|in:stripe,paypal,bank_transfer',
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Check if link has expired
        if ($invoice->payment_link_expires_at && $invoice->payment_link_expires_at->isPast()) {
            return back()->withErrors(['error' => 'Ce lien de paiement a expiré.']);
        }

        // Check remaining amount
        $remainingAmount = $invoice->total - $invoice->paid_amount;
        if ($validated['amount'] > $remainingAmount) {
            return back()->withErrors(['error' => 'Le montant dépasse le solde restant.']);
        }

        // For bank transfer, just record the intention
        if ($validated['payment_method'] === 'bank_transfer') {
            // Update invoice with bank transfer notification
            // Admin will manually confirm payment later
            return back()->with('success', 'Merci! Instructions de virement envoyées par email.');
        }

        // TODO: Integrate with Stripe or PayPal
        // For now, we'll just simulate success
        /*
        if ($validated['payment_method'] === 'stripe') {
            // Stripe::setApiKey(config('services.stripe.secret'));
            // $charge = Charge::create([
            //     'amount' => $validated['amount'] * 100,
            //     'currency' => strtolower($invoice->company->country->currency),
            //     'description' => "Payment for invoice {$invoice->invoice_number}",
            // ]);
        }
        */

        // Record payment
        $newPaidAmount = $invoice->paid_amount + $validated['amount'];
        $newStatus = $newPaidAmount >= $invoice->total ? 'paid' : $invoice->status;

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'status' => $newStatus,
        ]);

        return redirect()->route('payment.success', ['token' => $token]);
    }

    /**
     * Show payment success page
     */
    public function success(string $token): Response
    {
        $invoice = Invoice::where('payment_token', $token)->firstOrFail();

        return Inertia::render('Payment/Success', [
            'invoice' => $invoice,
        ]);
    }
}
