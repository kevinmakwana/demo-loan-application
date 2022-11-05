<?php

namespace Database\Factories;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanRepayments>
 */
class LoanRepaymentsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        //dd($this);
        $loanAmount = fake()->randomElement([1000,5000,10000,20000]);
        return [
            'amount' => $loanAmount,
            'repayment_date' => fake()->date('Y-m-d'),
            'status' => 'PENDING'
        ];
    }
}
