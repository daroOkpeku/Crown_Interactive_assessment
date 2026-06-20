<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\ProfileRepositoryInterface;
use App\Http\Requests\EditProfileRequest;
use App\Http\Resources\UserResource;
use App\Jobs\GeneralEventTrackerJob;
use App\Models\ProfileImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{

    public $profileinterface;
    public function __construct(ProfileRepositoryInterface $profileinterface)
    {

        $this->profileinterface = $profileinterface;
    }

    public function editprofile(EditProfileRequest $request)
    {
        $validated = $request->validated();
        return $this->profileinterface->editprofile($validated);
    }

    public function profileImage(Request $request)
    {
        $user = auth()->user();
        $profileImage = ProfileImage::where('user_id', $user->id)->first();
        if ($profileImage) {
            $url = asset('storage/' . $profileImage->image_path);
            return  apiResponse(200, 'Image uploaded successfully', $url);
        }
    }

    public function uploadProfileImage(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $user = auth()->user();
        $profileImage = ProfileImage::where('user_id', $user->id)->first();
        if ($profileImage && $profileImage->image_path) {
            Storage::disk('public')->delete($profileImage->image_path);
        }
        $path = $request->file('image')->store('images', 'public');
        if ($profileImage) {
            $profileImage->update(['image_path' => $path]);
            GeneralEventTrackerJob::dispatchSync(
                $user->roles()->first()?->id,
                "User Edit Image",
                "user_management",
                $user->id,
            );
        } else {
            ProfileImage::create([
                'user_id' => $user->id,
                'image_path' => $path,
            ]);
            GeneralEventTrackerJob::dispatchSync(
                $user->roles()->first()?->id,
                "User Upload Image ",
                "user_management",
                $user->id,
            );
        }
        $url = asset('storage/' . $path);
        return  apiResponse(200, 'Image uploaded successfully', $url);
    }


    public function fectchuserslist(Request $request)
    {
        $users = User::where('id', '!=', auth()->id())->get();
        $data = UserResource::collection($users);
        return  apiResponse(200, 'Users fetched successfully', $data);
    }
}
