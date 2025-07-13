<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\ApplicationForm;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApplicationFormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->json(ApplicationForm::all());
        $applications = ApplicationForm::with(['user', 'address'])->get();

        return response()->json($applications);
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
        $response = DB::transaction(function () use ($request) {
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

                if ($request->hasFile('valid_id')) {
                    $valid_id = $request->file('valid_id')->store('uploads', 'public');
                } else {
                    return response()->json(['error' => 'No file uploaded'], 400);
                }
                if ($request->hasFile('id_pic')) {
                    $id_pic = $request->file('id_pic')->store('uploads', 'public');
                }
                if ($request->hasFile('residence_proof')) {
                    $residence_proof = $request->file('residence_proof')->store('uploads', 'public');
                }
                if ($request->hasFile('income_proof')) {
                    $income_proof = $request->file('income_proof')->store('uploads', 'public');
                }

                $recordId = '2025-'. strtoupper(Str::random(8));
                $application = ApplicationForm::create([
                    'record_id' => $recordId,
                    'apply_status' => 'pending',
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'middle_name' => $request->middle_name,
                    'contact_num' => $request->contact_num,
                    'email' => $request->email,
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
                    'school' => $request->school,
                    'valid_id'=> $valid_id,
                    'id_pic' => $id_pic,
                    'residence_proof' => $residence_proof,
                    'income_proof' => $income_proof
                ]);

                // foreach ($request->transactions as $transaction) {
                //     $application->transactions()->create($transaction);
                // }
                foreach ($request->transactions as $transaction) {
                    $transactionData = json_decode($transaction, true);
                    $application->transactions()->create($transactionData);
                }

                $application->load('transactions.motorcycle');
                $application->update(['apply_status' => $this->eligibility($application)]);
                
                return response()->json([
                    'message' => 'Account was created successfully!',
                    'record_id' => $recordId,
                    'contact' => $request->contact_num
                ], 201);
            } catch(\Illuminate\Validation\ValidationException $e) {
                return response()->json(['errors ' => $e->errors()], 422);
            }
        });

        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApplicationForm  $application
     * @return \Illuminate\Http\Response
     */
    public function show($value)
    {
        // return response()->json($application->load(['transactions.motorcycle', 'address']));

        // $key = request()->query('by') === 'record_id' ? 'record_id' : 'id';
        $application = request()->query('by') === 'id'
            ? ApplicationForm::where('id', $value)->with(['transactions.motorcycle', 'address', 'ciReport'])->firstOrFail()
            : (request()->query('stff') == 'record_id'
                ? ApplicationForm::where(request()->query('by'), $value)->with('transactions.motorcycle')->firstOrFail()
                : ApplicationForm::where(request()->query('by'), $value)->firstOrFail()
            );

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
            $application->update([
                'apply_status' => $request->apply_status,
                'ci_id' => $request->ci_id
            ]);

            return response()->json([
                'message' => 'Data updated successfully',
                'data' => $application
            ]);
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

    private function empStability(float $rate, int $years): string
    {
        $inc = $rate >= 15000;
        $year = $years >= 1;

        return $inc && $year ? 'green' : ($inc || $year ? 'yellow' : 'red');
    }

    private function debtStability(float $loans, float $rent, float $amortization, float $rate): string
    {
        $dti = (($rent + $amortization + $loans) / $rate) * 100;

        return $dti <= 35 ? 'green' : ($dti > 35 && $dti < 46 ? 'yellow' : 'red');
    }

    private function ndiStability(float $loans, float $rate, float $rent, float $amortization, float $bills, float $living_exp, float $education_exp, float $transportation): string
    {
        $ndi = $rate - ($rent + $amortization + $bills + $living_exp + $education_exp + $transportation);
        $ndiBool = $loans / $ndi;

        return $ndiBool <= 0.3 ? 'green' : ($ndiBool > 0.3 && $ndiBool < 0.41 ? 'yellow' : 'red');
    }

    private function eligibility($arr): string
    {
        $loans = 0;
        $counts = ['green' => 0, 'yellow' => 0, 'red' => 0];

        foreach($arr->transactions as $unit) {
            $tenure = $unit->tenure * 12;
            $loanAmount = $unit->motorcycle->price - $unit->downpayment;
            $monthlyRate = $unit->motorcycle->interest / 12 / 100;
            $emi = $monthlyRate == 0 ? $loanAmount / $tenure
                : ($loanAmount * $monthlyRate * pow(1 + $monthlyRate, $tenure)) / (pow(1 + $monthlyRate, $tenure) - 1);

            $loans += $emi;
        }

        $empStability = $this->empStability($arr->rate, $arr->yrs_in_service);
        $debtStability = $this->debtStability($loans, $arr->rent, $arr->amortization, $arr->rate);
        $ndiStability = $this->ndiStability($loans, $arr->rate, $arr->rent, $arr->amortization, $arr->bills, $arr->living_exp, $arr->education_exp, $arr->transportation);

        foreach([$empStability, $debtStability, $ndiStability] as $i) {
            $counts[$i]++;
        }

        return ($counts['green'] === 3 || ($counts['green'] === 2 && $counts['yellow'] === 1) || ($counts['green'] === 1 && $counts['yellow'] === 2)) ? 'accepted'
            : (($counts['red'] === 3 || ($counts['red'] === 2 && $counts['yellow'] === 1) || ($counts['red'] === 1 && $counts['yellow'] === 2)) ? 'denied' : 'pending');
    }
}
