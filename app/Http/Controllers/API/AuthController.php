<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Requests\StoreLoginRequest;
use App\Http\Requests\StoreSignUpRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
   
class AuthController extends BaseAPIController
{
    public function login(StoreLoginRequest $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $authUser = Auth::user(); 
            $authUser->token =  $authUser->createToken('AspireTestApp')->plainTextToken; 

            return response()->success($this->loginSuccess, $this->success, $authUser);
        } 
        else{
            return response()->error($this->loginUnauthorized, $this->unauthorized);
        } 
    }

    public function register(StoreSignUpRequest $request)
    {
        $request->request->add(['password' => bcrypt($request->password)]);

        $user = User::create($request->all());
        $user->token =  $user->createToken('AspireTestApp')->plainTextToken;
   
        return response()->success($this->registrationSuccess, $this->success, $user);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->success($this->logoutSuccess, $this->success);
    }
   
}