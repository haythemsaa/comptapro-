<?php

namespace App\Models\Belgium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Company;

/**
 * Model pour les employés Belgique
 *
 * @property int $id
 * @property int $company_id
 * @property string $employee_number
 * @property string $first_name
 * @property string $last_name
 * @property string $niss NISS (Numéro d'Identification de la Sécurité Sociale)
 * @property string $email
 * @property string $phone
 * @property string $address
 * @property string $city
 * @property string $postal_code
 * @property string $marital_status
 * @property int $dependents
 * @property bool $has_disability
 * @property float $gross_monthly_salary
 * @property string $contract_type
 * @property \Carbon\Carbon $hire_date
 * @property \Carbon\Carbon|null $termination_date
 * @property string $status
 */
class EmployeeBE extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employees_be';

    protected $fillable = [
        'company_id',
        'employee_number',
        'first_name',
        'last_name',
        'niss',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'marital_status',
        'dependents',
        'has_disability',
        'gross_monthly_salary',
        'contract_type',
        'hire_date',
        'termination_date',
        'status',
        'bank_account_iban',
        'bank_account_bic',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
    ];

    protected $casts = [
        'gross_monthly_salary' => 'decimal:2',
        'dependents' => 'integer',
        'has_disability' => 'boolean',
        'hire_date' => 'date',
        'termination_date' => 'date',
    ];

    /**
     * Types de contrat
     */
    const CONTRACT_CDI = 'cdi'; // Contrat à durée indéterminée
    const CONTRACT_CDD = 'cdd'; // Contrat à durée déterminée
    const CONTRACT_INTERIM = 'interim'; // Intérimaire
    const CONTRACT_STUDENT = 'student'; // Job étudiant
    const CONTRACT_FREELANCE = 'freelance'; // Indépendant

    /**
     * Statuts matrimoniaux
     */
    const MARITAL_SINGLE = 'single';
    const MARITAL_MARRIED = 'married';
    const MARITAL_DIVORCED = 'divorced';
    const MARITAL_WIDOWED = 'widowed';
    const MARITAL_COHABITING = 'cohabiting'; // Cohabitant légal

    /**
     * Statuts employé
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_TERMINATED = 'terminated';

    /**
     * Relations
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payrolls()
    {
        return $this->hasMany(PayrollBE::class, 'employee_id');
    }

    /**
     * Accesseurs
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Valider le NISS (Numéro d'Identification de la Sécurité Sociale)
     * Format: YY.MM.DD-XXX.CC
     * où CC est la clé de contrôle
     */
    public static function validateNISS(string $niss): bool
    {
        // Enlever les points et tirets
        $niss = str_replace(['.', '-', ' '], '', $niss);

        // Doit contenir 11 chiffres
        if (!preg_match('/^\d{11}$/', $niss)) {
            return false;
        }

        // Vérifier la clé de contrôle
        $mainNumber = substr($niss, 0, 9);
        $checksum = (int) substr($niss, 9, 2);

        // Pour les personnes nées avant 2000
        $calculatedChecksum = 97 - ($mainNumber % 97);
        if ($calculatedChecksum == $checksum) {
            return true;
        }

        // Pour les personnes nées après 2000
        $mainNumber = '2' . $mainNumber;
        $calculatedChecksum = 97 - ($mainNumber % 97);
        return $calculatedChecksum == $checksum;
    }

    /**
     * Formater le NISS
     */
    public function getFormattedNissAttribute(): string
    {
        $niss = str_replace(['.', '-', ' '], '', $this->niss);
        return substr($niss, 0, 2) . '.' .
               substr($niss, 2, 2) . '.' .
               substr($niss, 4, 2) . '-' .
               substr($niss, 6, 3) . '.' .
               substr($niss, 9, 2);
    }

    /**
     * Calculer l'ancienneté en années
     */
    public function getYearsOfServiceAttribute(): int
    {
        return $this->hire_date->diffInYears(now());
    }

    /**
     * Calculer l'ancienneté en mois
     */
    public function getMonthsOfServiceAttribute(): int
    {
        return $this->hire_date->diffInMonths(now());
    }

    /**
     * Vérifier si l'employé est actif
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Calculer le salaire annuel brut
     */
    public function getAnnualGrossSalary(): float
    {
        return $this->gross_monthly_salary * 12;
    }

    /**
     * Calculer le pécule de vacances annuel
     * Pécule simple: 7.67% du salaire brut annuel
     * Pécule double: ~92% du salaire brut mensuel
     */
    public function getHolidayPay(): array
    {
        $simpleHolidayPay = round($this->getAnnualGrossSalary() * 0.0767, 2);
        $doubleHolidayPay = round($this->gross_monthly_salary * 0.92, 2);

        return [
            'simple' => $simpleHolidayPay,
            'double' => $doubleHolidayPay,
            'total' => $simpleHolidayPay + $doubleHolidayPay,
        ];
    }

    /**
     * Calculer le coût employeur annuel
     */
    public function getAnnualEmployerCost(): float
    {
        $annualGross = $this->getAnnualGrossSalary();
        $onss = $annualGross * PayrollBE::ONSS_EMPLOYER_AVERAGE_RATE;
        $holidayPay = $this->getHolidayPay()['total'];

        return $annualGross + $onss + $holidayPay;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByContractType($query, string $type)
    {
        return $query->where('contract_type', $type);
    }
}
