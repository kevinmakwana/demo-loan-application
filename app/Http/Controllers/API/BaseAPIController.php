<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class BaseAPIController extends Controller
{
    public $success = Response::HTTP_OK;                         //200
    public $created = Response::HTTP_CREATED;                    //201
    public $badRequest = Response::HTTP_BAD_REQUEST;             //400
    public $unauthorized = Response::HTTP_UNAUTHORIZED;          //401
    public $notFound = Response::HTTP_NOT_FOUND;                 //404
    public $entityError = Response::HTTP_UNPROCESSABLE_ENTITY;   //422
    public $serverError = Response::HTTP_INTERNAL_SERVER_ERROR;  //500

    public $okMessage = 'success';
    public $registrationSuccess = 'Your account has been created successfully.';
    public $loginSuccess = 'Login Successfully.';
    public $logoutSuccess = 'Logout successfully.';
    public $loginUnauthorized = "Credentials do not match.";

    public $lonsListing = 'Loans fetched successfully.';
    public $loanFetchSuccess = 'Loan fetched successfully.';
    public $loanStored = 'Loan created successfully.';
    public $loanAlreadyPaidOrApproved = 'Loan is already APPROVED/PAID';
    public $loanApproved = 'Loan approved successfully.';

    public $loanNotFound = 'Loan not found.';
    public $loanNotApproved = 'Loan is not approved yet.';
    public $repaymentAlreadyPaid = 'Loan installment already paid.';
    public $repaymentPaidSuccess = 'Your installment is paid successfully.';
}
