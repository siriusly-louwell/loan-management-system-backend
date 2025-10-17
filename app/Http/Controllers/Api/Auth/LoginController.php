<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input.',
                'type' => 'input',
                'errors' => $validator->errors()
            ]);
        }

        // Attempt authentication
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'type' => 'credentials'
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('react-app-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'type' => 'success',
            'role' => $user->role,
            'user' => $user,
            'token' => $token
        ]);
    }
}
