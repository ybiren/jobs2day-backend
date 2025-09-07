<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OccupationField;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    public function getUserProfile($user_id = null)
    {
        try {
            $user = User::with([
                'companyDetails' => function ($query) {
                    $query->when(auth()->user()?->type == 'business');
                },
                'occupationFields' => function ($query) {
                    $query->when(auth()->user()?->type != 'business');
                },
                'userAvailability' => function ($query) {
                    $query->when(auth()->user()?->type != 'business');
                }
            ])->find($user_id ?? auth()->id());

            // Check if the user exists
            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'User not found',
                    'data' => (object)[],
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'message' => 'User Data Fetched Successfully',
                'data' => $user,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error Fetching User',
                'data' => (object)[],
            ], 500);
        }
    }

    public function profileUpdate(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                // Common fields
                'full_name' => 'nullable|string|max:255',
                'password' => 'nullable|string|min:6|confirmed',
                'confirm_password' => 'nullable|string',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'gender' => 'nullable|string|max:10',
                'dob' => 'nullable|date',
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:6048',
                'description' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:20|unique:users,phone,' . auth()->id(),
                'type' => 'nullable|in:person,business',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'coordinates' => 'nullable|string|max:255',

                // Business fields
                'company_name' => 'nullable|string|max:255',
                'registration_no' => 'nullable|string|max:255',
                'field' => 'nullable|string|max:255',
                'details' => 'nullable|string|max:255',
                'company_email' => 'nullable|email',

                // Person fields
                'available_at' => 'nullable|string',
                'expected_min_salary' => 'nullable|string',
                'expected_max_salary' => 'nullable|string',
                'salary_type' => 'nullable|string',
                'are_you_mobile' => 'nullable|string',
                'occupation' => 'nullable|string',
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validated->errors()->first(),
                    'data' => (object)[],
                ], 422);
            }

            DB::beginTransaction();

            $user = auth()->user();

            // Handle name splitting if full_name is provided
            if ($request->filled('full_name')) {
                $nameParts = explode(' ', $request->full_name, 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';
            } else {
                $firstName = $request->first_name ?? $user->first_name;
                $lastName = $request->last_name ?? $user->last_name;
            }

            // Handle profile image upload
            $profileImage = $user->profile_image;
            if ($request->hasFile('profile_image')) {
                $profileImage = $this->uploadImage($request->file('profile_image'));
            }

            // Update user details
            $user->update([
                'password' => $request->filled('password') ? bcrypt($request->password) : $user->password,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'country' => $request->country ?? $user->country,
                'city' => $request->city ?? $user->city,
                'gender' => $request->gender ?? $user->gender,
                'dob' => $request->dob ?? $user->dob,
                'profile_image' => $profileImage,
                'description' => $request->description ?? $user->description,
                'phone' => $request->phone ?? $user->phone,
                'type' => $request->type ?? $user->type,
                'latitude' => $request->latitude ?? $user->latitude,
                'longitude' => $request->longitude ?? $user->longitude,
                'coordinates' => $request->coordinates ?? $user->coordinates,
            ]);

            // Update business-related fields
            if ($user->type === 'business') {
                $user->companyDetails()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $request->company_name ?? $user->companyDetails->company_name,
                        'company_email' => $request->company_email ?? $user->companyDetails->company_email,
                        'registration_no' => $request->registration_no ?? $user->companyDetails->registration_no,
                        'field' => $request->field ?? $user->companyDetails->field,
                        'details' => $request->details ?? $user->companyDetails->details,
                    ]
                );
            }

            // Update person-related fields
            if ($user->type === 'person') {
                $user->userAvailability()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'available_at' => $request->available_at ?? $user->userAvailability->available_at,
                        'expected_min_salary' => $request->expected_min_salary ?? null,
                        'expected_max_salary' => $request->expected_max_salary ?? null,
                        'are_you_mobile' => $request->are_you_mobile ?? $user->userAvailability->are_you_mobile,
                        'salary_type' => $request->salary_type ?? $user->userAvailability->salary_type,
                    ]
                );
            }

            // Update occupation fields
            if ($request->filled('occupation')) {
                $occupations = explode(',', $request->occupation);
                $user->occupationFields()->delete();
                foreach ($occupations as $occupation) {
                    $user->occupationFields()->create([
                        'field_name' => trim($occupation),
                    ]);
                }
            }

            DB::commit();
            $user = User::with([
                'companyDetails' => function ($query) {
                    $query->when(auth()->user()?->type == 'business');
                },
                'occupationFields' => function ($query) {
                    $query->when(auth()->user()?->type != 'business');
                },
                'userAvailability' => function ($query) {
                    $query->when(auth()->user()?->type != 'business');
                }, 'bankDetails'
            ])->find(auth()->id());

            return response()->json([
                'status' => 200,
                'message' => 'Profile updated successfully',
                'data' => $user,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during profile update: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Error during profile update',
                'data' => (object)[],
            ], 500);
        }
    }

    public function toggleNotification()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthorized',
                    'data' => (object)[],
                ], 401);
            }

            $user->update(['is_notifiable' => !$user->is_notifiable]);

            return response()->json([
                'status' => 200,
                'message' => 'Notification setting updated successfully.',
                'data' => ['is_notifiable' => $user->is_notifiable],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error during notification update',
                'data' => (object)[],
            ], 500);
        }
    }


    public function uploadImage(UploadedFile $image, $path = 'images/profile')
    {
        // Define the path to save the image directly in the public folder
        $path = public_path($path);

        // Create the directory if it doesn't exist
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // Generate a unique filename
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();

        // Store the image in the public/images/profile directory
        $image->move($path, $filename);

        // Return the relative URL to access the image
        return 'images/profile/' . $filename;
    }



}
