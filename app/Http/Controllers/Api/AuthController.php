<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use App\Models\UserBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\OccupationField;
use App\Models\UserAvailability;
use App\Models\CompanyDetail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function authentication(Request $request)
    {
        Log::error('start authentication');
        $validated = Validator::make($request->all(), [
            'auth_type' => 'required|in:facebook,apple,google,email',
            'auth_id' => 'nullable|string',
            'device_token' => 'nullable|string',
            'full_name' => 'nullable|string',
            'phone' => 'nullable|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'confirm_password' => 'nullable|same:password',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validated->errors()->first(),
                'data' => (object)[],
            ], 422);
        }
        $authType = $request->get('auth_type');
        $email = $request->get('email');
        $authId = $request->get('auth_id') ?? null;
        $fullName = $request->get('full_name');
        $nameParts = $fullName ? explode(' ', $fullName, 2) : [];
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $userAttributes = [
            'auth_id' => $authId,
            'auth_type' => $authType,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'is_onboarding_person' => '0',
            'is_onboarding_business' => '0',
            'avg_rating' => '0'
        ];

        $additionalAttributes = [];
        if ($authType == 'email') {
            if ($email) $additionalAttributes['email'] = $email;
            if ($request->get('phone')) $additionalAttributes['phone'] = $request->get('phone');
            if ($request->get('password')) $additionalAttributes['password'] = bcrypt($request->get('password'));
        }
        try {
            if ($authType == 'email') {
                $existingUser = User::where('auth_type', $authType)
                    ->where('email', $email)
                    ->first();

                if ($existingUser) {
                    return response()->json([
                        'status' => 409,
                        'message' => 'User with this email already exists.',
                        'data' => (object)[],
                    ], 409);
                }
            } elseif (in_array($authType, ['google', 'apple'])) {
                // Check if user already exists with Google or Apple auth_id
                $existingUser = User::where('auth_type', $authType)
                    ->where('auth_id', $authId)
                    ->first();

                if ($existingUser) {
                    return response()->json([
                        'status' => 409,
                        'message' => ucfirst($authType) . ' user already exists.',
                        'data' => (object)[],
                    ], 409);
                }
            }
            // Create or update the user
            if ($authType == 'email') {
                $user = User::create(array_merge($userAttributes, $additionalAttributes));
                $user = User::find($user->id);
                $otp = rand(100000, 999999);
                $user->update(['otp' => $otp]);

                Mail::send('emails.emailotp', ['otp' => $otp], function($message) use ($user) {
                    $message->to($user->email)
                        ->subject('אימות דוא"ל - Job2Day');
                });
            } else {
                Log::error('GGGG ');
                $user = User::updateOrCreate(
                    ['auth_id' => $authId, 'auth_type' => $authType], // Ensure matching auth_type
                    array_merge($userAttributes, $additionalAttributes)
                );
            }

            if ($request->has('device_token')) {
                $user->userDevices()->updateOrCreate(
                    ['user_id' => $user->id], // Values to update or insert
                    ['device_token' => $request->get('device_token')], // Search criteria
                );
            }


            // Generate token for the user
            $token = $user->createToken('jobs2day')->accessToken;

            // Ensure user data matches login response
            $userData = User::find($user->id)->makeHidden(['password', 'remember_token'])->toArray();

            return response()->json([
                'status' => 200,
                'message' => 'User registered and logged in successfully',
                'data' => [
                    'token' => $token,
                    'user' => $userData,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error while creating/updating user: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Internal Server Error',
                'data' => (object)[],
            ], 500);
        }
    }
    public function forgetPassword(Request $request)
    {
        try {
            // Validate email input
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validator->errors()->first(),
                    'data' => (object)[],
                ], 422);
            }

            // Find the user by email
            $user = User::where('email', $request->email)->first();

            // Generate OTP
            $otp = rand(100000, 999999);

            // Update user record with OTP
            $user->update(['otp' => $otp]);

            // Send OTP email using Laravel's built-in mail configuration
            Mail::send('emails.emailotp', ['otp' => $otp], function($message) use ($user) {
                $message->to($user->email)
                    ->subject('איפוס סיסמה - Job2Day');
            });
            return response()->json([
                'status' => 200,
                'message' => 'OTP sent successfully to your email.',
                'data' => (object)[],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong!',
                'data' => (object)[],
            ], 500);
        }
    }
    public function verifyEmailOtp(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'otp'   => 'required|digits:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validator->errors()->first(),
                    'data' => (object)[]
                ], 422);
            }

            // Find user by email and check OTP
            $user = User::where('email', $request->email)->where('otp', $request->otp)->first();

            if (!$user) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Invalid OTP.',
                    'data' => (object)[]
                ], 400);
            }

            // Clear OTP after verification
            $user->update(['otp' => null, 'email_verified_at' => now()]);

            return response()->json([
                'status' => 200,
                'message' => 'OTP verified successfully.',
                'data' => (object)[]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong!',
                'data' => (object)[]
            ], 500);
        }
    }
    public function resetPassword(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'password' => 'required|min:6|same:confirm_password', // Change 'confirmed' to 'same:confirm_password'
                'confirm_password' => 'required',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validator->errors()->first(),
                    'data' => (object)[],
                ], 422);
            }

            // Find user by email
            $user = User::where('email', $request->email)->first();

            // Update password
            $user->update([
                'password' => Hash::make($request->password),
                'otp' => null, // Clear OTP after reset
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Password reset successfully.',
                'data' => (object)[],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong!',
                'data' => (object)[],
            ], 500);
        }
    }
    /**
     * Log in an existing user.
     */
    public function login(Request $request)
    {
        try {
            // Validate the request
            $validated = Validator::make($request->all(), [
                'auth_type' => 'required|in:facebook,apple,google,email',
                'email' => 'nullable|email',
                'password' => 'nullable|string|min:6',
                'device_token' => 'nullable|string',
                'auth_id' => 'required_if:auth_type,google,apple|nullable|string',
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validated->errors()->first(),
                    'data' => (object)[],
                ], 422);
            }

            $authType = $request->get('auth_type');
            $authId = $request->get('auth_id');

            // Handle email authentication
            if ($authType == 'email') {
                $email = $request->get('email');
                $password = $request->get('password');

                if (!$email || !$password) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'Email and password are required for email login',
                        'data' => (object)[],
                    ], 422);
                }

                $user = User::with('userDevices', 'companyDetails', 'occupationFields', 'userAvailability', 'bankDetails')
                    ->where('email', $email)
                    ->first();

                if ($user && Hash::check($password, $user->password)) {
                    $token = $user->createToken('jobs2day')->accessToken;

                    if ($request->filled('device_token')) {
                        $user->userDevices()->updateOrCreate(
                            ['user_id' => $user->id],
                            ['device_token' => $request->get('device_token')]
                        );
                    }

                    return response()->json([
                        'status' => 200,
                        'message' => 'Login successful',
                        'data' => [
                            'token' => $token,
                            'user' => $user->makeHidden(['password']),
                        ],
                    ], 200);
                }

                return response()->json([
                    'status' => 422,
                    'message' => 'Invalid email or password',
                    'data' => (object)[],
                ], 422);
            }

            // Handle social logins
            if (in_array($authType, ['google', 'apple'])) {
                if (!$authId) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'auth_id is required for social login',
                        'data' => (object)[],
                    ], 422);
                }

                // First try to find user by social auth credentials
                $user = User::where('auth_id', $authId)
                    ->where('auth_type', $authType)
                    ->first();

                // If not found, check if email exists in database
                if (!$user && $request->has('email')) {
                    $user = User::where('email', $request->get('email'))
                        ->where('auth_type', 'email')
                        ->first();

                    if ($user) {
                        // Update existing email user with social auth credentials
                        $user->update([
                            'auth_id' => $authId,
                            'auth_type' => $authType
                        ]);
                    }
                }

                // If still no user found, create new one
                if (!$user) {
                    $email = $request->get('email');
                    $fullName = $request->get('full_name', '');

                    if (empty($fullName) && !empty($email)) {
                        $firstName = explode('@', $email)[0];
                        $lastName = '';
                    } else {
                        $nameParts = explode(' ', $fullName, 2);
                        $firstName = $nameParts[0] ?? '';
                        $lastName = $nameParts[1] ?? '';
                    }

                    $user = User::create([
                        'auth_id' => $authId,
                        'auth_type' => $authType,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'is_onboarding_person' => '0',
                        'is_onboarding_business' => '0',
                    ]);
                } elseif (!$user->email && $request->has('email')) {
                    $user->update(['email' => $request->get('email')]);
                }

                $token = $user->createToken('jobs2day')->accessToken;

                if ($request->has('device_token')) {
                    $user->userDevices()->updateOrCreate(
                        ['user_id' => $user->id],
                        ['device_token' => $request->get('device_token')]
                    );
                }

                return response()->json([
                    'status' => 200,
                    'message' => 'Login successful',
                    'data' => [
                        'token' => $token,
                        'user' => $user->makeHidden(['password']),
                    ],
                ], 200);
            }

            return response()->json([
                'status' => 400,
                'message' => 'Invalid auth type',
                'data' => (object)[],
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error processing request: ' . $e->getMessage(),
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
    public function onboardingPerson(Request $request)
    {
        try {
            // Validate input fields for person type
            $validated = Validator::make($request->all(), [
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
                'country' => 'nullable|string',
                'city' => 'nullable|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'coordinates' => 'nullable|string',
                'dob' => 'nullable|date',
                'gender' => 'nullable|string|in:male,female,other',
                'occupation' => 'nullable|string', // Can be multiple comma-separated values
                'available_at' => 'required|string', // Only required for person
                'expected_min_salary' => 'nullable|numeric',
                'expected_max_salary' => 'nullable|numeric',
                'are_you_mobile' => 'nullable|in:yes,no,flexible',
                'salary_type' => 'nullable|in:1,2,3,4',
            ]);

            // If validation fails, return the first validation error
            if ($validated->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validated->errors()->first(),
                    'data' => (object)[],
                ], 422);
            }

            // Start a database transaction to ensure rollback if any error occurs
            DB::beginTransaction();

            // Retrieve the current authenticated user
            $user = auth()->user();

            // Save data to the users table for person type
            $user->update([
                'profile_image' => $request->hasFile('profile_image')
                    ? $this->uploadImage($request->file('profile_image'))
                    : $user->profile_image,
                'country' => $request->country,
                'city' => $request->city,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'coordinates' => $request->coordinates,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'type' => 'person',
                'is_onboarding_person' => '1',
            ]);

            // Handle occupation fields for person
            if ($request->occupation) {
                $occupations = explode(',', $request->occupation); // Split comma-separated occupations
                foreach ($occupations as $occupation) {
                    OccupationField::create([
                        'user_id' => $user->id,
                        'field_name' => trim($occupation), // Trim spaces if any
                    ]);
                }
            }

            // Save user availability
            UserAvailability::create([
                'user_id' => $user->id,
                'available_at' => $request->available_at,
                'expected_min_salary' => $request->expected_min_salary ?? null,
                'expected_max_salary' => $request->expected_max_salary ?? null,
                'are_you_mobile' => $request->are_you_mobile,
                'salary_type' => $request->salary_type,
            ]);

            // Commit the transaction
            DB::commit();

            // Get the user details including related data for the response
            $user = User::with([
                'occupationFields' => function ($query) {
                    $query->when(auth()->user()?->type != 'business');
                },
                'userAvailability' => function ($query) {
                    $query->when(auth()->user()?->type != 'business');
                },
            ])->find(auth()->id());

            // Return success response with updated user data
            return response()->json([
                'status' => 200,
                'message' => 'Onboarding for person completed successfully',
                'data' => $user, // Return full user data here
            ], 200);

        } catch (\Exception $e) {
            // Rollback the transaction in case of any error
            DB::rollBack();

            // Log the error
            Log::error('Error during onboarding person: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Error during onboarding',
                'data' => (object)[],  // Empty data object
            ], 500);
        }
    }

    public function onboardingBusiness(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
                'country' => 'nullable|string',
                'city' => 'nullable|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'coordinates' => 'nullable|string',
                'company_name' => 'required|string',
                'registration_no' => 'required|string',
                'field' => 'required|string',
                'details' => 'nullable|string',
                'company_email' => 'nullable|string',
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

            $user->update([
                'profile_image' => $request->hasFile('profile_image')
                    ? $this->uploadImage($request->file('profile_image'))
                    : $user->profile_image,
                'country' => $request->country,
                'city' => $request->city,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'coordinates' => $request->coordinates,
                'company_email' => $request->company_email,
                'type' => 'business',
                'is_onboarding_business' => '1',
            ]);

            CompanyDetail::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name,
                'company_email' => $request->company_email,
                'registration_no' => $request->registration_no,
                'field' => $request->field,
                'details' => $request->details,
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Onboarding for business completed successfully',
                'data' => ['user' => $user],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during onboarding: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Error during onboarding',
                'data' => (object)[],
            ], 500);
        }
    }

    public function updateBankDetails(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Validate the request data
            $validatedData = $request->validate([
                'bank_name' => 'sometimes|string|max:255',
                'bank_branch' => 'sometimes|string|max:255',
                'account_holder_name' => 'sometimes|string|max:255',
                'account_no' => 'sometimes|string|max:255',
            ]);

            // Update or create bank details
            $bankDetails = UserBank::updateOrCreate(
                ['user_id' => $user->id],
                $validatedData
            );

            // If you need the full user with relationships (optional)
            $userWithRelations = User::with('userDevices', 'companyDetails', 'occupationFields', 'userAvailability', 'bankDetails')
                ->where('id', $user->id)
                ->first();

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Bank details updated successfully',
                'user' => $userWithRelations
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while updating bank details',
                'error' => $e->getMessage(),
                'data' => (object)[]
            ], 500);
        }
    }

    public function getVersion()
    {
        $appVersion = AppVersion::latest()->first();

        if (!$appVersion) {
            return response()->json([
                'status' => 404,
                'message' => 'App version not found',
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'App version retrieved successfully',
            'data' => $appVersion->version,
        ]);
    }



    public function logout(Request $request)
    {
        try {
            // Check if the user is authenticated
            if ($request->user()) {
                // Revoke the token
                $request->user()->token()->revoke();

                return response()->json([
                    'status' => 200,
                    'message' => 'Logout successful',
                    'data' => (object)[],
                ], 200);
            }

            return response()->json([
                'status' => 401,
                'message' => 'User not authenticated',
                'data' => (object)[],
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to logout',
                'data' => (object)[],
            ], 500);
        }
    }
    public function inactiveAccount()
    {
        try {
            // Check if the user is authenticated
            if (auth()->check()) {
                $user = auth()->user();
                $user->update(['is_deleted' => '1']);
                $user->token()->revoke();

                return response()->json([
                    'status' => 200,
                    'message' => 'User Deleted & Logout Successful',
                    'data' => (object)[],
                ], 200);
            }

            return response()->json([
                'status' => 401,
                'message' => 'User not authenticated',
                'data' => (object)[],
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to logout',
                'data' => (object)[],
            ], 500);
        }
    }

    public function DeleteAccount(Request $request)
    {
        try {
            $user = auth()->user(); // Get authenticated user

            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'User not authenticated',
                    'data' => (object)[],
                ], 401);
            }

            // Delete related data
            $user->userDevices()->delete();
            $user->occupationFields()->delete();
            $user->userAvailability()->delete();
            $user->companyDetails()->delete();
            $user->posts()->delete();
            $user->favoriteJobs()->delete();
            $user->reviewsGiven()->delete();
            $user->reviewsReceived()->delete();
            $user->transactions()->delete();

            // Finally, delete the user
            $user->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Account deleted successfully',
                'data' => (object)[],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong',
                'data' => (object)[],
            ], 500);
        }
    }



}
