<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'workflow_type',
        'status',
        'started_at',
        'completed_at',
        'duration_seconds',
        'parameters',
        'results',
        'error_message',
        'stack_trace',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'parameters' => 'array',
        'results' => 'array',
    ];

    /**
     * Relation avec l'entreprise
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope pour les logs réussis
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope pour les logs échoués
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope pour un type de workflow spécifique
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('workflow_type', $type);
    }

    /**
     * Obtient le taux de succès pour un type de workflow
     */
    public static function getSuccessRate(int $companyId, string $workflowType): float
    {
        $total = self::where('company_id', $companyId)
            ->where('workflow_type', $workflowType)
            ->count();

        if ($total === 0) {
            return 0;
        }

        $successful = self::where('company_id', $companyId)
            ->where('workflow_type', $workflowType)
            ->where('status', 'success')
            ->count();

        return ($successful / $total) * 100;
    }
}
