<?php

namespace App\Models\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'code',
        'name',
        'currency',
        'default_vat_rate',
        'accounting_plan',
        'vat_rates',
        'einvoicing_system',
        'vat_declaration_format',
        'is_active',
    ];

    protected $casts = [
        'vat_rates' => 'array',
        'default_vat_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all companies for this country
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Get the VAT rates as an array
     */
    public function getVatRatesArrayAttribute(): array
    {
        return $this->vat_rates ?? [];
    }
}
