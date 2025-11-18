<?php

namespace App\Models\Modules\Purchases\Models;

use App\Models\Modules\Core\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'supplier_number',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'country_code',
        'vat_number',
        'payment_term',
        'bank_account',
        'iban',
        'bic',
        'contact_person',
        'category',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    /**
     * Générer automatiquement le numéro de fournisseur
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->supplier_number)) {
                $lastSupplier = static::where('company_id', $supplier->company_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $nextNumber = $lastSupplier ? (int) substr($lastSupplier->supplier_number, 4) + 1 : 1;
                $supplier->supplier_number = 'SUP-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
