<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StoreWeeklyRepaymentRequest;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use App\Services\LoanService;
use Carbon\Carbon;

class LoanRepaymentController extends BaseAPIController
{
    
    public function payWeeklyInstallments(StoreWeeklyRepaymentRequest $request,$loanId,$loanRepaymentId){
       
        
        // STEP 1 : GET LOAN FROM DATABASE WITH LOAN REPAYMENT
        $loan = Loan::with(['loanRepayments' => function($query) use($loanRepaymentId){
            $query->whereId($loanRepaymentId)->first();
        }])->where(['id'=>$loanId])->first();

        // IF LOAN NOT FOUND THEN RETURN ERROR
        if(!$loan){
            return response()->error($this->loanNotFound, $this->notFound);
        }

        // IF LOAN STATUS IS NOT APPROVED RETURN MESSAGE
        if($loan->status !== 'APPROVED'){
            return response()->error($this->loanNotApproved, $this->entityError, new LoanResource($loan));
        }
        
        $loanRepayment = $loan->loanRepayments[0];

        // IF LOAN REPAYMENT IS ALREADY PAID
        if($loanRepayment->status !== 'PENDING'){                
            return response()->error($this->repaymentAlreadyPaid, $this->entityError, new LoanResource($loan));
        }

        $today = Carbon::now()->format('Y-m-d');

        // IF TODAY IS LOAN REPAYMENT DATE
        if($today == $loanRepayment->repayment_date){
            
            // CONVERT REQUESTED REPAYMENT AMOUNT TO FLOAT
            $installmentAmount = number_format($request->amount,2);
            
            // IF ITS LAST LOAN REPAYMENT AND INSTALLMENT AMOUNT IS GREATER THAN LOAN REPAYMENT AMOUNT THEN THROW ERROR
            if($loan->remaining_loan_term === 1 && ($installmentAmount > $loanRepayment->amount)){
                return response()->error('You are paying '.$installmentAmount.' which is greater than actual installment amount '.$loanRepayment->amount.'.', $this->entityError, new LoanResource($loan));
            }

            // IF REPAYMENT AMOUNT IS LESS THAN REQUIRED LOAN REPAYMENT AMOUNT DISPLAY MESSAGE
            if($installmentAmount < $loanRepayment->amount){
                return response()->error('You are paying '.$installmentAmount.' which is less than actual installment amount '.$loanRepayment->amount.'.', $this->entityError, new LoanResource($loan));
            }

            //CALCULATE REMAINING LOAN AMOUNT
            $remainingLoanAmount = $loan->remaining_amount_required - $installmentAmount;

            //IF INSTALLMENT AMOUNT IS GREATER THAN LOAN REPAYMENT AMOUNT

            if($installmentAmount > $loanRepayment->amount){

                //UPDATE OTHER LOAN REPAYMENT'S AMOUNT
                $this->updateLoanInstallmentAmount($loan,$remainingLoanAmount);
            }
            
            //UPDATE LOAN REPAYMENT STATUS TO PAID WITH PAID AMOUNT
            $this->updateLoanRepaymentStatus($loanRepayment,$installmentAmount);
            
            // UPDATE LOAN REMAINING REQUIRED AMOUNT AND LOAN TERM
            $this->updateLoan($loan,$remainingLoanAmount);
            
            
            // GET UPDATED LOAN DATA WITH RELATED DATA
            $loan->refresh();
            
            // IF REMAINING TERM AND REMAINING AMOUNT IS 0 THEN WHOLE LOAN UPDATE LOAN STATUS TO PAID
            if($loan->remaining_loan_term === 0 && $loan->remaining_amount_required === 0.0){
                $loan->update(['status' => 'PAID']); 
            }
            
            // SEND SUCCESSFUL INSTALLMENT PAID MESSAGE
            return response()->success($this->repaymentPaidSuccess, $this->success, new LoanResource($loan));
            
        }
        
        //ELSE RETURN LOAN WITH REPAYMENT DATE
        return response()->error('Loan installment date is '.$loanRepayment->repayment_date.'.', $this->entityError, new LoanResource($loan));
        
    }

    protected function updateLoan($loan,$remainingLoanAmount){
        $loan->update(['remaining_amount_required'=> $remainingLoanAmount,'remaining_loan_term' => ($loan->remaining_loan_term - 1)]);
    }

    protected function updateLoanRepaymentStatus($loanRepayment,$installmentAmount){
        $loanRepayment->update(['paid_amount' => $installmentAmount,'status'=>'PAID']);
    }

    protected function updateLoanInstallmentAmount($loan,$remainingLoanAmount){
        $newAmount = $remainingLoanAmount / ($loan->remaining_loan_term - 1);
        $loan->loanRepayments()->update(['amount' => $newAmount]);
    }
}
