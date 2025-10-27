<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Backoffice;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class AuthController extends Controller
{

    // Handle driver registration
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name'      => 'required|string|max:255',
                'username'       => 'required|string|unique:users,username',
                'email'          => 'required|email|unique:users,email',
                'phone'          => 'required|string|unique:users,phone',
                'password'       => 'required|string|min:8|confirmed',
                'role'           => 'required|in:admin,customer',
                'address'        => 'sometimes|string',
                'driver_license'           => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'profile_photo_path'       => 'sometimes|file|mimes:jpg,jpeg,png|max:1024',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // Check token validity
            $cacheKey = 'otp_token:' . $request->token;
            $cachedPhone = Cache::get($cacheKey);

            if (!$cachedPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification token.',
                    'debug'   => [
                        'provided_token' => $request->token,
                        'cache_key'      => $cacheKey,
                        'cached_value'   => $cachedPhone,
                        'note'           => 'No cached phone number found for this token (expired or never set).'
                    ]
                ], 403);
            }

            if ($cachedPhone !== $request->phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification token.',
                    'debug'   => [
                        'provided_phone' => $request->phone,
                        'cached_phone'   => $cachedPhone,
                        'note'           => 'Token exists but phone mismatch.'
                    ]
                ], 403);
            }
            Cache::forget($cacheKey);

            if ($request->hasFile('profile_photo_path')) {
                $file = $request->file('profile_photo_path');
                $fileName = time().'_'.$file->getClientOriginalName();
                $filePath = $file->storeAs('Profile_photos', $fileName, 'public');

                $profile_url = Storage::url($filePath);
            }

            if ($request->hasFile('driver_license')) {
                $file = $request->file('driver_license');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('Driver_License', $fileName, 'public');

                $license_url = Storage::url($filePath);
            }


            $roleName = ucfirst($request->role);
            $role = Role::where('name', $roleName)->firstOrFail();

            $user = User::create([
                'full_name' => $request->full_name,
                'username'  => $request->username,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'address'   => $request->address,
                'password'  => $request->password, // Automatic hashing via model cast
                'role_id'   => $role->id,
            ]);

            if ($roleName === 'Driver') {
                Driver::create([
                    'user_id'  => $user->id,
                    'profile_photo_path' => $profile_url ?? null,
                    'driver_license' => $license_url ?? null,
                    'job_title'=> 'Driver',
                ]);
            } else {
                // Rollback user creation if needed
                $user->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid role specified',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data'    => $user
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Handle customer registration
    public function register_customer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name'      => 'required|string|max:255',
                'username'       => 'required|string|unique:users,username',
                'email'          => 'required|email|unique:users,email',
                'phone'          => 'required|string|unique:users,phone',
                'password'       => 'required|string|min:8|confirmed',
                'address'        => 'sometimes|string',
                'id_photo_path_front'      => 'required|file|mimes:jpg,jpeg,png|max:2048',
                'id_photo_path_back'       => 'required|file|mimes:jpg,jpeg,png|max:2048',
                'profile_photo_path' => 'sometimes|file|mimes:jpg,jpeg,png|max:1024',
                'token' => 'sometimes|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // Check token validity
            $cacheKey = 'otp_token:' . $request->token;
            $cachedPhone = Cache::get($cacheKey);

            if (!$cachedPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification token.',
                    'debug'   => [
                        'provided_token' => $request->token,
                        'cache_key'      => $cacheKey,
                        'cached_value'   => $cachedPhone,
                        'note'           => 'No cached phone number found for this token (expired or never set).'
                    ]
                ], 403);
            }

            if ($cachedPhone !== $request->phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification token.',
                    'debug'   => [
                        'provided_phone' => $request->phone,
                        'cached_phone'   => $cachedPhone,
                        'note'           => 'Token exists but phone mismatch.'
                    ]
                ], 403);
            }
            Cache::forget($cacheKey);
            
            if ($request->hasFile('id_photo_path_front')) {
                $file = $request->file('id_photo_path_front');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('ID_photos_front', $fileName, 'private');
            
                $path_front = $filePath; // store path only
            }
            
            if ($request->hasFile('id_photo_path_back')) {
                $file = $request->file('id_photo_path_back');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('ID_photos_back', $fileName, 'private');
            
                $path_back = $filePath;
            }
            

            if ($request->hasFile('profile_photo_path')) {
                $file = $request->file('profile_photo_path');
                $fileName = time().'_'.$file->getClientOriginalName();
                $filePath = $file->storeAs('Profile_photos', $fileName, 'public');

                $profile_url = Storage::url($filePath);
            }

            $user = User::create([
                'full_name' => $request->full_name,
                'username'  => $request->username,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'address'   => $request->address,
                'password'  => $request->password, // Automatic hashing via model cast
                'role_id'   => Role::where('name', 'Customer')->firstOrFail()->id, // Get Customer role ID
            ]);

                Customer::create([
                    'user_id'            => $user->id,
                    'id_photo_path_front'      => $path_front ?? null,
                    'id_photo_path_back'       => $path_back ?? null,
                    'profile_photo_path' => $profile_url ?? null,
                    'is_verified'             => true,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Registered successfully'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed, Please try again',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update_profile(Request $request)
    {
        try {
            // Get authenticated user
            $user = Auth::user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            // Validation rules. Unique rules ignore current user id.
            $validator = Validator::make($request->all(), [
                'full_name'             => 'sometimes|string|max:255',
                'username'              => ['sometimes', 'string', Rule::unique('users', 'username')->ignore($user->id)],
                'email'                 => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
                'phone'                 => ['sometimes', 'string', Rule::unique('users', 'phone')->ignore($user->id)],
                'password'              => 'sometimes|string|min:8', // front-end handles confirmation
                'address'               => 'sometimes|string|nullable',
                'id_photo_path_front'   => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
                'id_photo_path_back'    => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
                'profile_photo_path'    => 'sometimes|file|mimes:jpg,jpeg,png|max:1024',
                'token'                 => 'sometimes|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // If phone is present in request, require token verification (OTP)
            if ($request->has('phone')) {
                if (! $request->filled('token')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Verification token required when updating phone.',
                    ], 403);
                }

                $cacheKey = 'otp_token:' . $request->token;
                $cachedPhone = Cache::get($cacheKey);

                if (! $cachedPhone) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or expired verification token.',
                        'debug' => [
                            'provided_token' => $request->token,
                            'cache_key'      => $cacheKey,
                            'cached_value'   => $cachedPhone,
                            'note'           => 'No cached phone number found for this token (expired or never set).'
                        ]
                    ], 403);
                }

                if ($cachedPhone !== $request->phone) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or expired verification token.',
                        'debug' => [
                            'provided_phone' => $request->phone,
                            'cached_phone'   => $cachedPhone,
                            'note'           => 'Token exists but phone mismatch.'
                        ]
                    ], 403);
                }

                // Token OK — remove from cache so it can't be reused
                Cache::forget($cacheKey);
            }

            // Start transaction for atomic update
            DB::beginTransaction();

            // Prepare user attributes to update
            $userUpdated = false;
            $userAttributes = [];

            if ($request->filled('full_name')) {
                $userAttributes['full_name'] = $request->full_name;
            }
            if ($request->filled('username')) {
                $userAttributes['username'] = $request->username;
            }
            if ($request->filled('email')) {
                $userAttributes['email'] = $request->email;
            }
            if ($request->filled('phone')) {
                $userAttributes['phone'] = $request->phone;
            }
            if ($request->filled('address')) {
                $userAttributes['address'] = $request->address;
            }
            if ($request->filled('password')) {
                $userAttributes['password'] = $request->password;
            }

            if (! empty($userAttributes)) {
                $user->fill($userAttributes);
                $user->save();
                $userUpdated = true;
            }

            // Ensure customer relation exists
            $customer = $user->customer;
            if (! $customer) {
                $customer = Customer::create(['user_id' => $user->id]);
            }

            // File handling: store new files, delete old when replaced
            // ID front
            if ($request->hasFile('id_photo_path_front')) {
                $file      = $request->file('id_photo_path_front');
                $fileName  = time() . '_front_' . $file->getClientOriginalName();
                $filePath  = $file->storeAs('ID_photos_front', $fileName, 'private');

                // delete old if exists
                if (!empty($customer->id_photo_path_front) && Storage::disk('private')->exists($customer->id_photo_path_front)) {
                    Storage::disk('private')->delete($customer->id_photo_path_front);
                }

                $customer->id_photo_path_front = $filePath;
            }

            // ID back
            if ($request->hasFile('id_photo_path_back')) {
                $file      = $request->file('id_photo_path_back');
                $fileName  = time() . '_back_' . $file->getClientOriginalName();
                $filePath  = $file->storeAs('ID_photos_back', $fileName, 'private');

                if (!empty($customer->id_photo_path_back) && Storage::disk('private')->exists($customer->id_photo_path_back)) {
                    Storage::disk('private')->delete($customer->id_photo_path_back);
                }

                $customer->id_photo_path_back = $filePath;
            }

            // Profile photo (public)
            if ($request->hasFile('profile_photo_path')) {
                $file      = $request->file('profile_photo_path');
                $fileName  = time() . '_profile_' . $file->getClientOriginalName();
                $filePath  = $file->storeAs('Profile_photos', $fileName, 'public');
                $profileUrl = Storage::url($filePath);

                // Delete old public profile file if it exists (assuming old stored path contains disk-relative path)
                if (!empty($customer->profile_photo_path)) {
                    // try to derive the storage path if previous path was a URL
                    $oldPath = $customer->profile_photo_path;
                    // If previous value starts with '/storage' or 'storage', remove leading '/storage' to get disk path
                    $diskPath = preg_replace('#^/storage/#', '', ltrim($oldPath, '/'));
                    if (Storage::disk('public')->exists($diskPath)) {
                        Storage::disk('public')->delete($diskPath);
                    }
                }
                $customer->profile_photo_path = $profileUrl;
            }

            // Optionally: mark as unverified if phone changed (business decision)
            // if ($request->filled('phone')) {
            //     $customer->is_verified = false;
            // }

            $customer->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data'    => [
                    'user'     => $user->fresh()->makeHidden(['password']),
                    'customer' => $customer->fresh(),
                ]
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update_driver(Request $request)
    {
        try {
            // Get authenticated user
            $user = Auth::user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            // Validation rules. Unique rules ignore current user id.
            $validator = Validator::make($request->all(), [
                'full_name'             => 'sometimes|string|max:255',
                'username'              => ['sometimes', 'string', Rule::unique('users', 'username')->ignore($user->id)],
                'email'                 => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
                'phone'                 => ['sometimes', 'string', Rule::unique('users', 'phone')->ignore($user->id)],
                'password'              => 'sometimes|string|min:8', // front-end handles confirmation
                'address'               => 'sometimes|string|nullable',
                'driver_license'        => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
                'profile_photo_path'    => 'sometimes|file|mimes:jpg,jpeg,png|max:1024',
                'token'                 => 'sometimes|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // If phone is present in request, require token verification (OTP)
            if ($request->has('phone')) {
                if (! $request->filled('token')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Verification token required when updating phone.',
                    ], 403);
                }

                $cacheKey = 'otp_token:' . $request->token;
                $cachedPhone = Cache::get($cacheKey);

                if (! $cachedPhone) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or expired verification token.',
                        'debug' => [
                            'provided_token' => $request->token,
                            'cache_key'      => $cacheKey,
                            'cached_value'   => $cachedPhone,
                            'note'           => 'No cached phone number found for this token (expired or never set).'
                        ]
                    ], 403);
                }

                if ($cachedPhone !== $request->phone) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or expired verification token.',
                        'debug' => [
                            'provided_phone' => $request->phone,
                            'cached_phone'   => $cachedPhone,
                            'note'           => 'Token exists but phone mismatch.'
                        ]
                    ], 403);
                }

                // Token OK — remove from cache so it can't be reused
                Cache::forget($cacheKey);
            }

            // Start transaction for atomic update
            DB::beginTransaction();

            // Prepare user attributes to update
            $userUpdated = false;
            $userAttributes = [];

            if ($request->filled('full_name')) {
                $userAttributes['full_name'] = $request->full_name;
            }
            if ($request->filled('username')) {
                $userAttributes['username'] = $request->username;
            }
            if ($request->filled('email')) {
                $userAttributes['email'] = $request->email;
            }
            if ($request->filled('phone')) {
                $userAttributes['phone'] = $request->phone;
            }
            if ($request->filled('address')) {
                $userAttributes['address'] = $request->address;
            }
            if ($request->filled('password')) {
                $userAttributes['password'] = $request->password;
            }

            if (! empty($userAttributes)) {
                $user->fill($userAttributes);
                $user->save();
                $userUpdated = true;
            }

            // Ensure driver relation exists
            $driver = $user->driver;
            if (! $driver) {
                $driver = Driver::create(['user_id' => $user->id]);
            }

            // File handling: store new files, delete old when replaced

            // ID back
            if ($request->hasFile('driver_license')) {
                $file      = $request->file('driver_license');
                $fileName  = time() . '_license_' . $file->getClientOriginalName();
                $filePath  = $file->storeAs('Driver_License', $fileName, 'private');

                if (!empty($customer->driver_license) && Storage::disk('private')->exists($driver->driver_license)) {
                    Storage::disk('private')->delete($driver->driver_license);
                }

                $driver->driver_license = $filePath;
            }

            // Profile photo (public)
            if ($request->hasFile('profile_photo_path')) {
                $file      = $request->file('profile_photo_path');
                $fileName  = time() . '_profile_' . $file->getClientOriginalName();
                $filePath  = $file->storeAs('Profile_photos', $fileName, 'public');
                $profileUrl = Storage::url($filePath);

                // Delete old public profile file if it exists (assuming old stored path contains disk-relative path)
                if (!empty($driver->profile_photo_path)) {
                    // try to derive the storage path if previous path was a URL
                    $oldPath = $driver->profile_photo_path;
                    // If previous value starts with '/storage' or 'storage', remove leading '/storage' to get disk path
                    $diskPath = preg_replace('#^/storage/#', '', ltrim($oldPath, '/'));
                    if (Storage::disk('public')->exists($diskPath)) {
                        Storage::disk('public')->delete($diskPath);
                    }
                }
                $driver->profile_photo_path = $profileUrl;
            }

            // Optionally: mark as unverified if phone changed (business decision)
            // if ($request->filled('phone')) {
            //     $customer->is_verified = false;
            // }

            $driver->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data'    => [
                    'user'     => $user->fresh()->makeHidden(['password']),
                    'driver' => $driver->fresh(),
                ]
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Handle backoffice registration separately
    // public function registerBackoffice(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'username' => 'required|string|unique:backoffices,username',
    //             'password'  => 'required|string|min:8|confirmed',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Validation errors',
    //                 'errors'  => $validator->errors()
    //             ], 422);
    //         }

    //         $account = Backoffice::create([
    //             'password' => Hash::make($request->password), // Automatic hashing via model cast
    //             'username'  => $request->username,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Backoffice account created successfully',
    //             'data'    => $account
    //         ], 201);

    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Backoffice registration failed',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // Handle login for staff and backoffice users
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('username', 'password');
            
            $user = User::where('username', $credentials['username'])->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account does not exist',
                ], 404);
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is banned',
                ], 403);
            }

            $customttl = 60 * 24 * 30; // 43200

            // Attempt authentication and issue token with 30-day TTL
            if (! $token = auth('api')->setTTL($customttl)->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }
            // Remove sensitive fields
            $userData = $user->makeHidden(['password', 'remember_token'])->toArray();
            // Replace role_id with role name
            $role = $user->role()->first();
            $userData['role'] = $role ? $role->name : null;
            unset($userData['role_id']);

            // If the user is a staff member, fetch the job title
            if ($user->role->name === 'Admin' || $user->role->name === 'Driver') {
                $driver = Driver::where('user_id', $user->id)->first();
                if ($driver) {
                    $userData['job_title'] = $driver->job_title;
                } else {
                    $userData['job_title'] = null;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token'   => $token,
                'expires_in' => $customttl * 60,
                'user'    => $userData

            ]);
    
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Handle login for customers
    public function login_customer(Request $request)
    {
        try {
            $credentials = $request->only('phone', 'password');

            $customttl = 60 * 24 * 30; // 43200

            if (! $token = auth('api')->setTTL($customttl)->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }
            // Check if user exists
            // Find the user by phone
            $user = User::where('phone', $credentials['phone'])->first();

            // If user exists, try to get the related customer profile and photo path
            $customerProfile = null;
            $profilePhotoPath = null;
            if ($user) {
                $customerProfile = Customer::where('user_id', $user->id)->first();
                if ($customerProfile && $customerProfile->profile_photo_path) {
                    $profilePhotoPath = config('app.url') . $customerProfile->profile_photo_path;
                }
            }
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account does not exist',
                ], 404);
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is banned',
                ], 403);
            }

            // Remove sensitive fields
            $userData = $user->makeHidden(['password', 'remember_token']);

            // Replace role_id with role name
            $role = $user->role()->first();
            $userData['role'] = $role ? $role->name : null;
            unset($userData['role_id']);


            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token'   => $token,
                'expires_in' => $customttl * 60, // seconds
                'user'    => $userData,
                'profile_photo_path' => $profilePhotoPath
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Handle logout for all users
    public function logout(Request $request)
    {
        try {
            auth('api')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Handle token refresh
    public function refresh(Request $request)
    {
        try {
            $customttl = 60 * 24 * 30; // 43200

            // Set TTL for the new token, then refresh (this will by default blacklist the old token)
            $token = auth('api')->setTTL($customttl)->refresh();

            return response()->json([
                'success'    => true,
                'message'    => 'Token refreshed successfully',
                'token'      => $token,
                'expires_in' => $customttl * 60
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not refresh token',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Fetch the authenticated user's details
    public function me()
    {
        try {
            $user = auth('api')->user();
            // Send more specific user data and concatinate the first and last name
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Hide sensitive fields
            $userData = $user->makeHidden(['password', 'remember_token'])->toArray();

            // Add role name
            $role = $user->role()->first();
            $userData['role'] = $role ? $role->name : null;
            unset($userData['role_id']);

            // Add extra info based on role
            switch ($userData['role']) {
                case 'Customer':
                    $customer = Customer::where('user_id', $user->id)->first();
                    if ($customer) {
                        $userData['id_photo_path_front'] = $customer->id_photo_path_front;
                        $userData['id_photo_path_back'] = $customer->id_photo_path_back;
                        $userData['profile_photo_path'] = $customer->profile_photo_path;
                        $userData['is_verified'] = $customer->is_verified;
                    }
                    break;
                case 'Admin':
                case 'Driver':
                    $driver = Driver::where('user_id', $user->id)->first();
                    if ($driver) {
                        $userData['driver_license'] = $driver->driver_license;
                        $userData['profile_photo_path'] = $driver->profile_photo_path;
                        $userData['job_title'] = $driver->job_title;
                    }
                    break;
            }

            $user = $userData;

            return response()->json([
                'success' => true,
                'message' => 'User fetched successfully',
                'data'    => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not fetch user',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Handle password reset for customers
    // It needs a configuration of CACHE_DRIVER=array  # for local testing and use redi for production change in .env file
    public function forgot(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
            'token' => 'required|string',
        ]);

        // Check token validity
        $cacheKey = 'otp_token:' . $request->token;
        $cachedPhone = Cache::get($cacheKey);

        if (!$cachedPhone) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification token.',
            ], 403);
        }
        if ($cachedPhone !== $request->phone) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification token.',
            ], 403);
        }
        Cache::forget($cacheKey);

        // Find user
        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Update password
        $user->password = $request->password;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful.',
        ]);
    }

}
