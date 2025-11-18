<?php

namespace App\Models;

use App\Models\Modules\Invoicing\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceReminder extends Model
{
    protected $fillable = [
        'invoice_id',
        'type',
        'days_overdue',
        'sent_at',
        'sent_to',
        'message',
        'opened',
        'opened_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'opened' => 'boolean',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
