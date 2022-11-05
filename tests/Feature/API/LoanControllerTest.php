<?php

namespace Tests\Feature\API;

use App\Http\Resources\LoanResource;
use App\Models\Loan;
use App\Models\LoanRepayments;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoanControllerTest extends TestCase
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

    public function test_non_authenticated_user_cannot_see_loans()
    {
        $response = $this->json('GET',config('app.url').'/api/loans',[],['Accept' => 'application/json']);
        
        $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized.']);
    }

    public function test_show_list_of_own_loans_as_normal_user()
    {
        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $response = $this->json('GET',config('app.url').'/api/loans',[],['Accept' => 'application/json']);

        $response->assertSuccessful()
        ->assertJson(['message' => 'Loans fetched successfully.'])
        ->assertJsonStructure([
            'data' => [
                '*' => [
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
            ]
        ]); 
    }

    public function test_show_list_of_all_loans_as_admin()
    {
        Sanctum::actingAs(
            User::where('email','testAdmin@mailinator.com')->first(),
        );

        $response = $this->json('GET',config('app.url').'/api/loans');

        $response->assertSuccessful()
        ->assertJson(['message' => 'Loans fetched successfully.'])
        ->assertJsonStructure([
            'data' => [
                '*' => [
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
            ]
        ]);
    }

    public function test_show_user_loan(){
        $user = Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $loan = Loan::first();
        $response = $this->json('GET',config('app.url').'/api/loans/'.$loan->id,[],['Accept' => 'application/json']);

        $response->assertSuccessful()
        ->assertJson(['message' => 'Loan fetched successfully.'])
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
        ])
        ->assertJsonPath('data.id',$loan->id)
        ->assertJsonPath('data.user_id',$user->id); 
    }

    public function test_show_validation_error_when_all_fields_empty_at_creation_of_loan()
    {
        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $response = $this->json('POST',config('app.url').'/api/loans/create', [
            'amount_required' => '',
            'loan_term' => ''
        ],['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'amount_required',
            'loan_term'
        ]])
        ->assertJsonPath('message.amount_required.0','The amount required field is required.')
        ->assertJsonPath('message.loan_term.0','The loan term field is required.');
    }

    public function test_show_validation_error_when_amount_required_field_is_empty_at_creation_of_loan()
    {
        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $response = $this->json('POST',config('app.url').'/api/loans/create', [
            'amount_required' => ''
        ],['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'amount_required'
        ]])
        ->assertJsonPath('message.amount_required.0','The amount required field is required.');
    }

    public function test_show_validation_error_when_loan_term_field_is_empty_at_creation_of_loan()
    {
        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $response = $this->json('POST',config('app.url').'/api/loans/create', [
            'loan_term' => ''
        ],['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'loan_term'
        ]])
        ->assertJsonPath('message.loan_term.0','The loan term field is required.');
    }

    public function test_user_successfully_created_loan()
    {
        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $response = $this->json('POST',config('app.url').'/api/loans/create', [
            'amount_required' => 5000.55,
            'loan_term' => 3
        ],['Accept' => 'application/json']);

        $response->assertStatus(201)
        ->assertJson(['message' => 'Loan created successfully.'])
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

    public function test_only_admin_can_approve_for_loan()
    {
        Sanctum::actingAs(
            User::where('email','testAdmin@mailinator.com')->first(),
        );

        $loan = Loan::first();

        $response = $this->json('PUT',config('app.url').'/api/loans/approve-loan/'.$loan->id,[],['Accept' => 'application/json']);
       
        $response->assertStatus(200)
                    ->assertJson(['message' => 'Loan approved successfully.'])
                    ->assertJsonPath('data.status','APPROVED');
        
    }

    public function test_throw_error_when_loan_is_already_approved_or_paid()
    {
        Sanctum::actingAs(
            User::where('email','testAdmin@mailinator.com')->first(),
        );

        $loan = Loan::first();
        $loan->update(['status' => 'APPROVED']);

        $response = $this->json('PUT',config('app.url').'/api/loans/approve-loan/'.$loan->id,[],['Accept' => 'application/json']);
       
        $response->assertStatus(200)
                    ->assertJson(['message' => 'Loan is already APPROVED/PAID']);
        
    }

    public function test_throw_error_if_user_is_trying_to_approve_loan()
    {
        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $loan = Loan::first();

        $response = $this->json('PUT',config('app.url').'/api/loans/approve-loan/'.$loan->id,[],['Accept' => 'application/json']);
        
        $response->assertStatus(401)
                    ->assertJson(['message' => 'Unauthorized.']);
    }

    public function test_throw_error_if_loan_not_found_at_approve_loan()
    {
        Sanctum::actingAs(
            User::where('email','testuser@mailinator.com')->first(),
        );

        $loanId = 10;

        $response = $this->json('PUT',config('app.url').'/api/loans/approve-loan/'.$loanId,[],['Accept' => 'application/json']);
        
        $response->assertStatus(404)
                    ->assertJson(['message' => 'Record does not exist into database.']);
    }
    
}
