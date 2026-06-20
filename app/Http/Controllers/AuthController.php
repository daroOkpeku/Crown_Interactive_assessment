<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\AuthRepositoryInterface;
use App\Http\Requests\ForgotRequest;
use App\Http\Requests\Loginrequuest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Jobs\GeneralEventTrackerJob;
use Illuminate\Http\Request;
use App\Models\Verification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Token;

class AuthController extends Controller
{




    public $authinterface;
    public function __construct(AuthRepositoryInterface $authinterface)
    {

        $this->authinterface = $authinterface;
    }


    public  function Register(RegisterRequest $request)
    {
        $validated = $request->validated();
        return $this->authinterface->Register($validated);
    }


    public function login(Loginrequuest $request)
    {
        $validated = $request->validated();
        return $this->authinterface->login($validated);
    }

    public function forgot_password(ForgotRequest $request)
    {
        $validated = $request->validated();
        return $this->authinterface->forget_password($validated);
    }



    public  function verify_email(Request $request)
    {
        $validated = $request->validate([
            'verification' => 'required|exists:verifications,code',
        ]);

        $verification =  Verification::where('code', $validated['verification'])->first();
        if ($verification->is_used) {
            return response()->json(['error' => 'this email has been verified']);
        }
        DB::connection()->beginTransaction();
        $verification = Verification::where('code', $validated['verification'])->first();
        if ($verification) {
            $verification->update(['is_used' => true]);

            $user = User::find($verification->user_id);
            if ($user) {
                $user->update(['is_verified' => true]);
            }
        }
        GeneralEventTrackerJob::dispatchSync(
            null,
            "User email verifield for " . $user->id,
            "user_management",
            $user->id,
        );
        DB::commit();
        return  apiResponse(200, 'Your email has been verified successfully', null);
    }


    public function fetchuserdata()
    {
        return apiResponse(200, 'User data fetched successfully', auth()->user());
    }


    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens()->update(['revoked' => true]);
        GeneralEventTrackerJob::dispatchSync(
            auth()->user()->roles()->first()?->id,
            "User logout for " . $user->id,
            "user_management",
            $user->id,
        );

        return apiResponse(200, 'Logged out', null);
    }

    public function reset_password(ResetPasswordRequest $request)
    {
        $validated = $request->validated();
        return $this->authinterface->reset_password($validated);
    }
}
