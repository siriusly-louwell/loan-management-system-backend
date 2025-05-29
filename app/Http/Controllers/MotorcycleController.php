<?php

namespace App\Http\Controllers;

use App\Models\Motorcycle;
use Illuminate\Http\Request;

class MotorcycleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(Motorcycle::all());
        $motorcycles = Motorcycle::with('colors')->get();
        return response()->json($motorcycles);
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
            $validated = $request->validate([
                'name' => 'required|string',
                'brand' => 'required|string',
                // 'color' => 'required|string',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
                'interest' => 'required|integer',
                'tenure' => 'required|integer',
                'rebate' => 'required|numeric',
                'file' => 'required|file|mimes:jpg,jpeg,png',
                'colors' => 'required|array',
                'colors.*' => 'required|string',
            ]);

            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('uploads', 'public');
            } else {
                return response()->json(['error' => 'No file uploaded'], 400);
            }

            $motor = Motorcycle::create([
                'name' => $validated['name'],
                'brand' => $validated['brand'],
                // 'color' => $validated['color'],
                'price' => $validated['price'],
                'description' => $validated['description'],
                'quantity' => $validated['quantity'],
                'rebate' => $validated['rebate'],
                'tenure' => $validated['tenure'],
                'interest' => $validated['interest'],
                'file_path' => $filePath,
            ]);

            // $motor = new Motorcycle();
            // $motor->name = $validated['name'];
            // $motor->brand = $validated['brand'];
            // $motor->color = $validated['color'];
            // $motor->file_path = $filePath;
            // $motor->save();

            foreach ($validated['colors'] as $color) {
                $motor->colors()->create(['color' => $color]);
            }
        
            return response()->json([
                'message' => 'Product was created successfully!',
                'file_path' => $filePath
            ], 201);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors ' => $e->errors(),
                'var_colors' => $request->colors
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Motorcycle  $motorcycle
     * @return \Illuminate\Http\Response
     */
    public function show(Motorcycle $motorcycle)
    {
        $motorcycle->load('colors'); // eager load colors
        return response()->json($motorcycle);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Motorcycle  $motorcycle
     * @return \Illuminate\Http\Response
     */
    public function edit(Motorcycle $motorcycle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Motorcycle  $motorcycle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Motorcycle $motorcycle)
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
     * @param  \App\Models\Motorcycle  $motorcycle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Motorcycle $motorcycle)
    {
        //
    }
}
