<?php

namespace Database\Factories\Belgium;

use App\Models\Belgium\EmployeeBE;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeBEFactory extends Factory
{
    protected $model = EmployeeBE::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'employee_number' => 'EMP-' . $this->faker->unique()->numberBetween(1000, 9999),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'niss' => $this->generateNISS(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->numberBetween(1000, 9999),
            'marital_status' => $this->faker->randomElement(['single', 'married', 'divorced', 'widowed', 'cohabiting']),
            'dependents' => $this->faker->numberBetween(0, 4),
            'has_disability' => $this->faker->boolean(10),
            'gross_monthly_salary' => $this->faker->numberBetween(2000, 6000),
            'contract_type' => $this->faker->randomElement(['cdi', 'cdd', 'interim', 'student']),
            'hire_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']),
            'bank_account_iban' => 'BE' . $this->faker->numerify('## #### #### ####'),
            'bank_account_bic' => $this->faker->regexify('[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?'),
        ];
    }

    /**
     * Générer un NISS belge valide
     */
    protected function generateNISS(): string
    {
        // Format: YY.MM.DD-XXX.CC
        $birthDate = $this->faker->dateTimeBetween('-65 years', '-18 years');
        $year = $birthDate->format('y');
        $month = $birthDate->format('m');
        $day = $birthDate->format('d');
        $sequence = $this->faker->numberBetween(1, 999);

        $mainNumber = $year . $month . $day . str_pad($sequence, 3, '0', STR_PAD_LEFT);

        // Calcul checksum
        $checksum = 97 - ($mainNumber % 97);

        return $year . '.' . $month . '.' . $day . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT) . '.' . str_pad($checksum, 2, '0', STR_PAD_LEFT);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function cdi(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_type' => 'cdi',
        ]);
    }
}
