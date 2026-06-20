<?php

namespace App\Http\Repository;

use App\Http\Repository\Contracts\ProfileRepositoryInterface;
use App\Jobs\GeneralEventTrackerJob;
use App\Models\User;

class ProfileRepository implements ProfileRepositoryInterface
{

    public function editprofile($validated)
    {
        $user = User::find(auth()->id());
        if ($user) {
            $user->update([
                'firstname' => $validated['firstname'] ? $validated['firstname'] : $user->firstname,
                'lastname' => $validated['lastname'] ? $validated['lastname'] : $user->lastname,
            ]);
            GeneralEventTrackerJob::dispatchAfterResponse(
                $user->role->id,
                "User Edit Profile",
                "user_management",
                $user->id,
            );

            return  apiResponse(200, 'you have edited your account', []);
        }
    }
}
