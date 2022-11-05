<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StoreChangeLoanStatusRequest;
use App\Http\Requests\StoreCreateLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use App\Services\LoanService;
use Carbon\Carbon;  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends BaseAPIController
{

    /** @var LoanService */
    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }
    
    // GET ALL LOANS OF USER
    public function index(Request $request){
                
        $loans = $this->loanService->getLoans($request);

        return response()->success($this->lonsListing, $this->success, LoanResource::collection($loans));        
    }

    public function show($id){

        $loan = $this->loanService->getLoan($id);

        return response()->success($this->loanFetchSuccess, $this->success, new LoanResource($loan));
    }

    // CREATE LOAN FOR USER
    public function store(StoreCreateLoanRequest $request){
        $loan = $this->loanService->createLoanWithLoanRepayment($request);
        return response()->success($this->loanStored, $this->created, new LoanResource($loan));
    }

    // ONLY ADMIN USER CAN CHANGE STATUS TO APPROVE FOR LOAN - MIDDLEWARE IS APPLIED
    public function changeStatus(StoreChangeLoanStatusRequest $request,$id){
        $response = $this->loanService->changeStatus($id,$request);
        return $response ? response()->success($this->loanApproved, $this->success) : response()->error($this->loanAlreadyPaidOrApproved, $this->entityError);
    }
}
