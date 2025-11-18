<?php

namespace App\Models\Modules\Purchases\Models;

use App\Models\Modules\Core\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'supplier_id',
        'invoice_number',
        'supplier_invoice_number',
        'type',
        'status',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'paid_amount',
        'notes',
        'payment_terms',
        'created_by',
        'received_at',
        'approved_at',
        'approved_by',
        'ocr_data',
        'original_filename',
        'file_path',
        'ocr_processed',
        'ocr_confidence',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'received_at' => 'datetime',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'ocr_confidence' => 'decimal:2',
        'ocr_processed' => 'boolean',
        'ocr_data' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceLine::class)->orderBy('line_order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Générer automatiquement le numéro de facture d'achat
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $prefix = $invoice->type === 'credit_note' ? 'PCN' : 'PUR';
                $lastInvoice = static::where('company_id', $invoice->company_id)
                    ->where('type', $invoice->type)
                    ->orderBy('id', 'desc')
                    ->first();

                $nextNumber = $lastInvoice ? (int) substr($lastInvoice->invoice_number, strlen($prefix) + 1) + 1 : 1;
                $invoice->invoice_number = $prefix . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Vérifier si la facture est payée
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->paid_amount >= $this->total;
    }

    /**
     * Obtenir le solde restant
     */
    public function getRemainingBalanceAttribute(): float
    {
        return max(0, $this->total - $this->paid_amount);
    }
}
