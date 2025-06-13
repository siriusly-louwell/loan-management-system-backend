<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ApplicationForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(User::all());
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
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string',
                'role' => 'required|string',
                'status' => 'required|string'
            ]);

            $arr = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'],
                'status' => $validatedData['status'],
            ];
    
            if($validatedData['role'] == 'customer') {
                $application = ApplicationForm::where('record_id', $request->record_id)->firstOrFail();


                if($application->apply_status == "approved") {
                    $user = User::create($arr);
                    $application->user_id = $user->id;
                    $application->save();
                    
                } else return response()->json(['message' => 'Your account is not approved yet', 'type' => 'invalid']);
            } else {
                $user = User::create($arr);
            }
    
            return response()->json(['message' => 'Account was created successfully!', 'type' => 'valid'], 201);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors ' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

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
            $validatedData = $request->validate([
                'name' => 'sometimes|string',
                'brand' => 'sometimes|string',
                'color' => 'sometimes|string',
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric',
                'quantity' => 'sometimes|integer',
                'file_path' => 'sometimes|string'
            ]);
    
            $motorcycle->update($validatedData);
    
            return response()->json(['message' => 'Product was created successfully!'], 201);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors ' => $e->errors()], 422);
        }
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
