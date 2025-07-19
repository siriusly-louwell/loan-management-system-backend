<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CiReport;
use App\Models\ApplicationForm;
use Illuminate\Http\Request;

class CiReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
                'birth_day' => 'required|string',
                'birth_place' => 'required|string',
                'father_first' => 'required|string',
                'father_middle' => 'required|string',
                'father_last' => 'required|string',
                'mother_first' => 'required|string',
                'mother_middle' => 'required|string',
                'mother_last' => 'required|string',
                'comm_standing' => 'required|string',
                'home_description' => 'required|string',
                'sketch' => 'required|file|mimes:jpg,jpeg,png',
            ]);

            if ($request->hasFile('sketch')) {
                $sketch = $request->file('sketch')->store('uploads', 'public');
            }

            $validated['application_form_id'] = $request->application_id;
            $validated['recommendation'] = $request->recommendation;
            $validated['remarks'] = $request->remarks;
            $validated['first_unit'] = $request->first_unit;
            $validated['delivered'] = $request->delivered;
            $validated['outlet'] = $request->outlet;
            $validated['sketch'] = $sketch;

            $motor = CiReport::create($validated);
            $application = ApplicationForm::where('id', $request->application_id)->firstOrFail();

            if($application->apply_status == "accepted") {
                $application->apply_status = "evaluated";
                $application->save();
                    
            } else return response()->json(['message' => 'This account is not approved yet', 'type' => 'invalid']);
        
            return response()->json(['message' => 'Report saved successfully!'], 201);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors ' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CiReport  $report
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $response = CiReport::where('application_form_id', $id)->firstOrFail();
        
        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
