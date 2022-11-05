<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'amount_required',
        'remaining_amount_required',
        'loan_term',
        'remaining_loan_term',
        'status'
    ];

    protected $with = ['loanRepayments'];

    public function users(){
        return $this->belongsTo(User::class);
    }

    public function loanRepayments(){
        return $this->hasMany(LoanRepayments::class,'loan_id','id');
    }
}
