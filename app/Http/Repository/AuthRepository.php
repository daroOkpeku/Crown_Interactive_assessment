<?php

namespace App\Http\Repository;

use App\Events\SendMessageEvent;
use App\Http\Repository\Contracts\AuthRepositoryInterface;
use App\Jobs\ForgotPasswordJob;
use App\Jobs\GeneralEventTrackerJob;
use App\Jobs\RegisterJob;
use App\Mail\SendRegisteremail;
use App\Models\ForgotPassword;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthRepository implements AuthRepositoryInterface
{
    public function generatecode()
    {
        $randnum = rand(00000, 99999);
        $time = now();


        $randnum_first_three = substr($randnum, 0, 3);
        $time_seconds = $time->format('s');
        $time_first_three = substr($time_seconds, 0, 3);


        $joined_value = $randnum_first_three . $time_first_three;
        return $joined_value;
    }

    public function Register($validated)
    {
        DB::connection()->beginTransaction();
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $verification =  Verification::create([
            'code' => $this->generatecode(),
            'is_used' => false,
            'user_id' => $user->id
        ]);


        RegisterJob::dispatch($user->firstname, $user->lastname, $user->email,  $verification->code);
        GeneralEventTrackerJob::dispatchSync(
            null,
            "User Registration",
            "user_management",
            $user->id,
        );
        DB::commit();

        return apiResponse(200, 'your created suuccessfully', []);
    }


    public function login($validated)
    {
        $emailcheck = User::where('email', $validated['email'])->first();

        if (!$emailcheck->is_verified) {
            return apiResponseError(403, 'Your email is not confirmed');
        }

        if (!Hash::check($validated['password'], $emailcheck->password)) {
            return apiResponseError(401, 'Invalid credentials');
        }
        $token = $emailcheck->createToken('auth-token')->accessToken;

        $data = [
            'email' => $emailcheck->email,
            'firstname' => $emailcheck->firstname,
            'lastname' => $emailcheck->lastname,
            'id' => $emailcheck->id,
            'token' => $token
        ];
        GeneralEventTrackerJob::dispatchSync(
            null,
            "User Login",
            "user_management",
            $emailcheck->id,
        );
        return apiResponse(200, 'Your have logged in successfully', $data);
    }

    public function forget_password($validated)
    {

        $user = User::where('email', $validated['email'])->first();

        $generatecode = sha1(time());
        $forget =  ForgotPassword::create([
            'user_id' => $user->id,
            'code' => $generatecode,
            'is_used' => false
        ]);

        GeneralEventTrackerJob::dispatchSync(
            null,
            "User Forgot Password",
            "user_management",
            $user->id,
        );

        ForgotPasswordJob::dispatch($forget->code, $user->email);
        return apiResponse(200, 'please check your email to reset your password', []);
    }


    public function reset_password($validated)
    {
        $forgetpassword = ForgotPassword::where('code', $validated['code'])->first();

        $forgetpassword->update([
            'is_used' => true
        ]);

        $user = $forgetpassword->user;
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);
        GeneralEventTrackerJob::dispatchSync(
            null,
            "User Reset Password",
            "user_management",
            $user->id,
        );
        return apiResponse(200, 'you password has been changed', []);
    }
}
