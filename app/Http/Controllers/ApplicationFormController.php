<?php

namespace App\Http\Controllers;

use App\Models\ApplicationForm;
use Illuminate\Http\Request;

class ApplicationFormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $data = ApplicationForm::all();
        return response()->json(ApplicationForm::all());
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
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'middle_name' => 'required|string',
                'gender' => 'required|string',
                'status' => 'required|string',
                'educ_attain' => 'required|string',
                'residence' => 'required|string',
                'amortization' => 'required|numeric',
                'rent' => 'required|numeric',
                'sss' => 'required|string',
                'tin' => 'required|string',
                'income' => 'required|string',
                'superior' => 'required|string',
                'employment_status' => 'required|string',
                'yrs_in_service' => 'required|integer',
                'rate' => 'required|string',
                'employer' => 'required|string',
                'salary' => 'required|string',
                'business' => 'required|string',
                'living_exp' => 'required|string',
                'rental_exp' => 'required|string',
                'education_exp' => 'required|string',
                'transportation' => 'required|string',
                'insurance' => 'required|string',
                'bills' => 'required|string',
                'spouse_name' => 'required|string',
                'b_date' => 'required|string',
                'spouse_work' => 'required|string',
                'children_num' => 'required|string',
                'children_dep' => 'required|string',
                'school' => 'required|string'
            ]);
    
            $motor = ApplicationForm::create($validatedData);
    
            return response()->json(['message' => 'Product was created successfully!'], 201);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors ' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApplicationForm  $application
     * @return \Illuminate\Http\Response
     */
    public function show(ApplicationForm $application)
    {
        return response()->json($application);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ApplicationForm  $application
     * @return \Illuminate\Http\Response
     */
    public function edit(ApplicationForm $application)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ApplicationForm  $application
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ApplicationForm $application)
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
    
            $application->update($validatedData);
    
            return response()->json(['message' => 'Product was created successfully!'], 201);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors ' => $e->errors()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApplicationForm  $application
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApplicationForm $application)
    {
        //
    }
}
