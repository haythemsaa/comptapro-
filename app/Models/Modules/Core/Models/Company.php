<?php

namespace App\Models\Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'country_id',
        'name',
        'legal_name',
        'registration_number',
        'vat_number',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'logo_path',
        'fiscal_year_start',
        'fiscal_year_end',
        'accounting_plan_type',
        'subscription_plan',
        'subscription_expires_at',
        'is_active',
    ];

    protected $casts = [
        'fiscal_year_start' => 'date',
        'fiscal_year_end' => 'date',
        'subscription_expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the country for this company
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get all users for this company
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get all accounts for this company
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(\App\Models\Modules\Accounting\Models\Account::class);
    }

    /**
     * Get all journals for this company
     */
    public function journals(): HasMany
    {
        return $this->hasMany(\App\Models\Modules\Accounting\Models\Journal::class);
    }

    /**
     * Get all customers for this company
     */
    public function customers(): HasMany
    {
        return $this->hasMany(\App\Models\Modules\Invoicing\Models\Customer::class);
    }

    /**
     * Get all invoices for this company
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(\App\Models\Modules\Invoicing\Models\Invoice::class);
    }

    /**
     * Check if subscription is active
     */
    public function hasActiveSubscription(): bool
    {
        if (!$this->subscription_expires_at) {
            return true;
        }

        return $this->subscription_expires_at->isFuture();
    }

    /**
     * Get subscription status
     */
    public function getSubscriptionStatusAttribute(): string
    {
        if (!$this->hasActiveSubscription()) {
            return 'expired';
        }

        return 'active';
    }
}
