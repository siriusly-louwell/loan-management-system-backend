<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\ApplicationForm;
use App\Models\Address;
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
        DB::transaction(function () use ($request) {
            try {
                // $validateAddress = $request->validate([
                //     'personal_pres' => 'required|string',
                //     'personal_prev' => 'required|string',
                //     'parent_pres' => 'required|string',
                //     'parent_prev' => 'required|string',
                //     'spouse_pres' => 'required|string',
                //     'spouse_prev' => 'required|string',
                //     'employer_address' => 'required|string',
                // ]);

                $address = Address::create([
                    'personal_pres' => $request->personal_pres,
                    'personal_prev' => $request->personal_prev,
                    'parent_pres' => $request->parent_pres,
                    'parent_prev' => $request->parent_prev,
                    'spouse_pres' => $request->spouse_pres,
                    'spouse_prev' => $request->spouse_prev,
                    'employer_address' => $request->employer_address,
                ]);
                
                // $validatedData = $request->validate([
                    // 'first_name' => 'required|string',
                    // 'last_name' => 'required|string',
                    // 'middle_name' => 'required|string',
                    // 'gender' => 'required|string',
                    // 'status' => 'required|string',
                    // 'educ_attain' => 'required|string',
                    // 'residence' => 'required|string',
                    // 'amortization' => 'required|numeric',
                    // 'rent' => 'required|numeric',
                    // 'sss' => 'required|string',
                    // 'tin' => 'required|string',
                    // 'income' => 'required|string',
                    // 'superior' => 'required|string',
                    // 'employment_status' => 'required|string',
                    // 'yrs_in_service' => 'required|integer',
                    // 'rate' => 'required|string',
                    // 'employer' => 'required|string',
                    // 'salary' => 'required|string',
                    // 'business' => 'required|string',
                    // 'living_exp' => 'required|string',
                    // 'rental_exp' => 'required|string',
                    // 'education_exp' => 'required|string',
                    // 'transportation' => 'required|string',
                    // 'insurance' => 'required|string',
                    // 'bills' => 'required|string',
                    // 'spouse_name' => 'required|string',
                    // 'b_date' => 'required|string',
                    // 'spouse_work' => 'required|string',
                    // 'children_num' => 'required|string',
                    // 'children_dep' => 'required|string',
                    // 'school' => 'required|string'
                // ]);

                $motor = ApplicationForm::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'middle_name' => $request->middle_name,
                    'address_id' => $address->id,
                    'gender' => $request->gender,
                    'status' => $request->status,
                    'educ_attain' => $request->educ_attain,
                    'residence' => $request->residence,
                    'amortization' => $request->amortization,
                    'rent' => $request->rent,
                    'sss' => $request->sss,
                    'tin' => $request->tin,
                    'income' => $request->income,
                    'superior' => $request->superior,
                    'employment_status' => $request->employment_status,
                    'yrs_in_service' => $request->yrs_in_service,
                    'rate' => $request->rate,
                    'employer' => $request->employer,
                    'salary' => $request->salary,
                    'business' => $request->business,
                    'living_exp' => $request->living_exp,
                    'rental_exp' => $request->rental_exp,
                    'education_exp' => $request->education_exp,
                    'transportation' => $request->transportation,
                    'insurance' => $request->insurance,
                    'bills' => $request->bills,
                    'spouse_name' => $request->spouse_name,
                    'b_date' => $request->b_date,
                    'spouse_work' => $request->spouse_work,
                    'children_num' => $request->children_num,
                    'children_dep' => $request->children_dep,
                    'school' => $request->school
                ]);
                
                return response()->json(['message' => 'Product was created successfully!'], 201);
                return response()->json([
                    'application' => $motor,
                    'address' => $address
                ], 201);
            } catch(\Illuminate\Validation\ValidationException $e) {
                return response()->json(['errors ' => $e->errors()], 422);
            }
        });
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
