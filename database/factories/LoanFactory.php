<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanRepayments;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $loanAmount = fake()->randomElement([1000,5000,10000,20000]);
        $loanTerm = fake()->randomElement([2,3,4]);

        return [
            'user_id' => User::factory(),
            'amount_required' => $loanAmount,
            'remaining_amount_required' => $loanAmount,
            'loan_term' => $loanTerm,
            'remaining_loan_term' => $loanTerm,
            'status' => 'PENDING'
        ];
    }

    public function withLoanRepayments()
    {

        return $this->afterCreating(function (Loan $loan) {
            $loanRepaymentAmount = ($loan->amount_required/$loan->loan_term);
            
            $now = Carbon::now();

            for($i = 1; $i<= $loan->loan_term; $i++){
                $repaymentDate = $now->addDays(7);

                LoanRepayments::factory()->create([
                    'loan_id' => $loan->id,
                    'amount' => $loanRepaymentAmount,
                    'repayment_date' => $repaymentDate->format('Y-m-d')
                ]);
            }
        });
    }
}
