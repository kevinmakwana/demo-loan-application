<?php

namespace Tests\Feature\API;

use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoanRepaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp() :void
    {
        parent::setUp();

        User::factory()->create([
            'email' => 'testAdmin@mailinator.com',
            'password' => Hash::make('testpassword'),
            'is_admin' => 1
        ]);

        // create a user
        $user = User::factory()->create([
            'email' => 'testuser@mailinator.com',
            'password' => Hash::make('testpassword')
        ]);

        $user2 = User::factory()->create([
            'email' => 'testuser2@mailinator.com',
            'password' => Hash::make('testpassword'),
            'is_admin' => 0
        ]);

        Loan::factory()->withLoanRepayments()->count(2)->create([
            'user_id' => $user->id
        ]);

        Loan::factory()->withLoanRepayments()->count(3)->create([
            'user_id' => $user2->id
        ]);

    }
    
    public function test_throw_error_if_amount_field_is_empty_at_loan_repayment()
    {

        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $loan = Loan::with('loanRepayments')->first();
        $loanRepaymentId = $loan->loanRepayments->first()->id;

        $response = $this->json('PUT',
                                config('app.url').'/api/loans/'.$loan->id.'/installment/'.$loanRepaymentId,
                                ['amount' => ''],
                                ['Accept' => 'application/json']);

        $response->assertStatus(422)
                    ->assertJsonPath('message.amount.0','The amount field is required.');
    }

    public function test_throw_error_if_loan_not_found_on_loan_repayment()
    {

        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $loanId = 10;
        $loanRepaymentId = 1;

        $response = $this->json('PUT',
                                config('app.url').'/api/loans/'.$loanId.'/installment/'.$loanRepaymentId,
                                ['amount' => '300'],
                                ['Accept' => 'application/json']);
       
        $response->assertStatus(404)
        ->assertJson(['message' => 'Loan not found.']);

    }

    public function test_throw_error_if_loan_is_not_approved_at_loan_repayment()
    {

        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $loan = Loan::with('loanRepayments')->first();
        
        $loanRepaymentId = $loan->loanRepayments->first()->id;

        $response = $this->json('PUT',
                                config('app.url').'/api/loans/'.$loan->id.'/installment/'.$loanRepaymentId,
                                ['amount' => '400'],
                                ['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJson(['message' => 'Loan is not approved yet.']);
    }

    public function test_throw_error_if_loan_repayments_is_already_paid_at_loan_repayment()
    {

        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $loan = Loan::with('loanRepayments')->first();
        $loan->update(['status'=>'APPROVED']);
        
        $loanRepayment = $loan->loanRepayments->first();
        $loanRepayment->update(['status'=>'PAID']);

        $response = $this->json('PUT',
                                config('app.url').'/api/loans/'.$loan->id.'/installment/'.$loanRepayment->id,
                                ['amount' => '400'],
                                ['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJson(['message' => 'Loan installment already paid.']);
    }

    public function test_throw_error_if_loan_repayments_is_not_today_at_loan_repayment()
    {

        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $loan = Loan::with('loanRepayments')->first();
        $loan->update(['status'=>'APPROVED']);
        
        $loanRepayment = $loan->loanRepayments->first();

        $response = $this->json('PUT',
                                config('app.url').'/api/loans/'.$loan->id.'/installment/'.$loanRepayment->id,
                                ['amount' => '400'],
                                ['Accept' => 'application/json']);
        
        $response->assertStatus(422)
        ->assertJson(['message' => 'Loan installment date is '.$loanRepayment->repayment_date.'.']);
    }

    public function test_throw_error_if_loan_remaining_loan_terms_is_one_and_installment_amount_is_greater_then_repayment_amount_at_loan_repayment()
    {

        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $loan = Loan::with('loanRepayments')->first();
        $loan->update(['status'=>'APPROVED','remaining_loan_term'=>1]);
        
        $loanRepayment = $loan->loanRepayments->first();
        $loanRepayment->update(['amount'=>200,'repayment_date' => Carbon::now()->format('Y-m-d')]);

        $amount = 400;
        $response = $this->json('PUT',
                                config('app.url').'/api/loans/'.$loan->id.'/installment/'.$loanRepayment->id,
                                ['amount' => $amount],
                                ['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJson(['message' => 'You are paying '.$amount.' which is greater than actual installment amount '.$loanRepayment->amount.'.']);
    }

    public function test_throw_error_if_installment_amount_is_less_then_repayment_amount_at_loan_repayment()
    {

        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $loan = Loan::with('loanRepayments')->first();
        $loan->update(['status'=>'APPROVED','remaining_loan_term'=>1]);
        
        $loanRepayment = $loan->loanRepayments->first();
        $loanRepayment->update(['amount'=>600,'repayment_date' => Carbon::now()->format('Y-m-d')]);

        $amount = 400;
        $response = $this->json('PUT',
                                config('app.url').'/api/loans/'.$loan->id.'/installment/'.$loanRepayment->id,
                                ['amount' => $amount],
                                ['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJson(['message' => 'You are paying '.$amount.' which is less than actual installment amount '.$loanRepayment->amount.'.']);
    }

    public function test_pay_repayment_installment_amount_successfully_at_loan_repayment()
    {

        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $loan = Loan::with('loanRepayments')->first();
        $loan->update(['status'=>'APPROVED','remaining_loan_term'=>1]);
        
        $loanRepayment = $loan->loanRepayments->first();
        $loanRepayment->update(['amount'=>600,'repayment_date' => Carbon::now()->format('Y-m-d')]);

        $amount = 600;
        $response = $this->json('PUT',
                                config('app.url').'/api/loans/'.$loan->id.'/installment/'.$loanRepayment->id,
                                ['amount' => $amount],
                                ['Accept' => 'application/json']);

        $response->assertStatus(200)
        ->assertJson(['message' => 'Your installment is paid successfully.'])
        ->assertJsonStructure([
            'data' => [
                        'id',
                        'user_id',
                        'amount_required',
                        'loan_term',
                        'status',
                        'loan_repayments' => [
                            '*' => [
                                'loan_id',
                                'amount',
                                'repayment_date',
                                'status'
                            ]
                        ]
                
            ]
        ]);
    }
}
