<?php

namespace App\Services;

use App\Http\Resources\LoanResource;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanService{

    public function getLoans(Request $request){

        $user = $request->user();

        $query = Loan::query();
        
        // IF LOGIN USER IS REGULAR USER THAN HE IS ONLY ABLE TO VIEW HIS OWN LOANS
        if(!$user->isAdmin()){
            $query->where('user_id',$user->id);
        }

        $loans = $query->latest()->get();

        return $loans;

    }

    public function getLoan($id){
       return Loan::findOrFail($id);
    }

    public function createLoanWithLoanRepayment($request){

        $user = $request->user();       
        $loanAmountRequired = $request->amount_required;
        $loanTerm = $request->loan_term;
        $repaymentAmounts = round($loanAmountRequired/$loanTerm,2);
        $now = Carbon::now();
        $repaymentData = [];
        
        // IF THERE IS ANY ERROR IN CREATION OF LOAN ROLL BACK ALL DATA 
        // ELSE STORE LOAN AND LOAN REPAYMENTS INTO DATABASE
        try {
            DB::beginTransaction();

            $loan = Loan::create([
                'user_id' => $user->id,
                'amount_required' => $loanAmountRequired,
                'remaining_amount_required' => $loanAmountRequired,
                'loan_term' => $loanTerm,
                'remaining_loan_term' => $loanTerm
            ]);
            
            for($i = 1; $i<= $loanTerm; $i++){
                $repaymentDate = $now->addDays(7);
                array_push($repaymentData,[
                    'loan_id' => $loan->id,
                    'amount' => $repaymentAmounts,
                    'repayment_date' => $repaymentDate->format('Y-m-d')
                ]);
            }

            $loan->loanRepayments()->createMany($repaymentData);
            
            DB::commit();

            return $loan;

        } catch(\Exception $e) {
            DB::rollBack(); 

            throw $e;
            
        }
    }

    public function changeStatus($id,$request){

        $loan = $this->getLoan($id);

        if(!in_array($loan->status,['APPROVED','PAID'])){
            $loan->update(['status'=>'APPROVED']);
            return true;
        }
        return false;
    } 
   
}