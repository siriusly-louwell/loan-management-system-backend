<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ApplicationForm;
use App\Notifications\TemporaryPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 8);
        $users = User::query();

        if ($request->has('role')) {
            $role = $request->input('role');

            $users->when($role, function ($query, $role) {
                $query->where('role', $role);
            });
        }

        if ($request->has('search')) {
            $search = $request->input('search');

            $users->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('gender', 'like', "%{$search}%");
                });
            });
        }

        if ($request->has('min') || $request->has('max')) {
            $min = $request->input('min');
            $max = $request->input('max');
            $type = $request->input('type');

            $users->when($min, fn($q) => $q->where($type, '>=', $min))
                ->when($max, fn($q) => $q->where($type, '<=', $max));
        }

        return response()->json($users->orderBy('created_at', 'desc')->paginate($perPage));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = [];

        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email',
                'gender' => 'required|string',
                'password' => 'required|string',
                'role' => 'required|string',
                'status' => 'required|string',
            ]);

            if (User::where('email', $validatedData['email'])->exists()) {
                return response()->json([
                    'message' => 'An account with this email already exists',
                    'type' => 'warn'
                ]);
            }

            $arr = [
                'first_name' => $validatedData['first_name'],
                'middle_name' => $request->middle_name,
                'last_name' => $validatedData['last_name'],
                'gender' => $validatedData['gender'],
                'contact' => $request->contact,
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'],
                'status' => $validatedData['status']
            ];

            if ($validatedData['role'] == 'customer') {
                $application = ApplicationForm::where('record_id', $request->record_id)->firstOrFail();

                if ($application->apply_status == "approved") {
                    $arr['pfp'] = $application->id_pic;
                    $arr['gender'] = $application->gender;

                    $user = User::create($arr);
                    $application->user_id = $user->id;
                    $application->save();
                    $application->credits()->update(['user_id' => $user->id]);
                } else return response()->json(['message' => 'Your account is not approved yet', 'type' => 'invalid']);
            } else {
                // $pfp = ($request->hasFile('pfp')) ? $request->file('pfp')->store('uploads', 'public') : null;

                // $arr['pfp'] = $pfp;
                $user = User::create($arr);
            }

            $user->notify(new TemporaryPassword(
                $arr['first_name'] . ' ' . $arr['last_name'],
                $arr['role'],
                $request->password
            ));

            return response()->json([
                'message' => 'Account was created successfully!',
                'type' => 'success',
                'data' => $user,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors ' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $account)
    {
        return response()->json($account);
    }

    // public function account(Request $request)
    // {
    //     Log::info("here");
    //     return response()->json(Auth::user());
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        try {
            $updateType = $request->input('type');

            Log::info($request->input('current_password'));

            if ($updateType === 'password')
                return $this->updatePassword($request, $user);
            else return $this->updateUserDetails($request, $user);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Update user password
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    private function updatePassword(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Verify current password is correct
        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'type' => 'error'
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validatedData['new_password'])
        ]);

        return response()->json([
            'message' => 'Password updated successfully',
            'type' => 'success',
            'data' => $user
        ]);
    }

    /**
     * Update user details (name, email, contact, gender, etc.)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    private function updateUserDetails(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'first_name' => 'sometimes|string',
            'middle_name' => 'sometimes|string|nullable',
            'last_name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'contact' => 'sometimes|string|nullable',
            'gender' => 'sometimes|string',
            'status' => 'sometimes|string',
            'pfp' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle profile picture upload if provided
        if ($request->hasFile('pfp')) {
            // Optionally delete old profile picture if exists
            // Storage::delete($user->pfp);
            $validatedData['pfp'] = $request->file('pfp')->store('uploads', 'public');
        }

        // Update only provided fields
        $user->update($validatedData);

        return response()->json([
            'message' => 'User details updated successfully',
            'type' => 'success',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $motorcycle
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
