<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\ApplicationForm;
use App\Models\Address;
use App\Notifications\ApplicationStatus;
use App\Models\Schedule;
use App\Notifications\ApplicationSubmitted;
use App\Notifications\PaymentReminder;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApplicationFormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 8);
        $applications = ApplicationForm::with(['user', 'address']);

        // ? Customer-specific filter
        $applications->when($request->input('isCustomer') !== 'false', function ($query) use ($request) {
            $query->where('user_id', $request->input('isCustomer'));
        });

        // ? Filter by statuses
        $applications->when($request->filled('statuses'), function ($query) use ($request) {
            $statuses = $request->input('statuses');
            $query->whereIn('apply_status', $statuses);
        });

        // ? Search filter
        if ($request->has('search')) {
            $search = $request->input('search');

            $applications->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('record_id', 'like', "%{$search}%")
                        ->orWhere('apply_status', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('contact_num', 'like', "%{$search}%")
                        ->orWhere('tin', 'like', "%{$search}%")
                        ->orWhere('sss', 'like', "%{$search}%");
                });
            });
        }

        // ? Min/max filter
        if ($request->has('min') || $request->has('max')) {
            $min = $request->input('min');
            $max = $request->input('max');
            $type = $request->input('type');

            $applications->when($min, fn($q) => $q->where($type, '>=', $min))
                ->when($max, fn($q) => $q->where($type, '<=', $max));
        }

        return response()->json($applications->orderBy('created_at', 'desc')->paginate($perPage));
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
                $address = Address::create([
                    'personal_pres' => $request->personal_pres,
                    'personal_prev' => $request->personal_prev,
                    'parent_pres' => $request->parent_pres,
                    'parent_prev' => $request->parent_prev,
                    'spouse_pres' => $request->spouse_pres,
                    'spouse_prev' => $request->spouse_prev,
                    'employer_address' => $request->employer_address,
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                ]);

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

                $recordId = '2025-' . strtoupper(Str::random(8));
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
                    'birth_day' => $request->birth_day,
                    'birth_place' => $request->birth_place,
                    'father_first' => $request->father_first,
                    'father_middle' => $request->father_middle,
                    'father_last' => $request->father_last,
                    'mother_first' => $request->mother_first,
                    'mother_middle' => $request->mother_middle,
                    'mother_last' => $request->mother_last,
                    'comm_standing' => $request->comm_standing,
                    'home_description' => $request->home_description,
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
                    'valid_id' => $valid_id,
                    'id_pic' => $id_pic,
                    'residence_proof' => $residence_proof,
                    'income_proof' => $income_proof,
                ]);

                $transactionData = json_decode($request->transaction, true);
                $application->transactions()->create($transactionData);

                $application->notify(new ApplicationSubmitted(
                    $request->first_name . ' ' . $request->last_name,
                    $recordId,
                    $transactionData
                ));

                return response()->json([
                    'message' => 'Application was submitted successfully!',
                    'type' => "success",
                    'record_id' => $recordId,
                    'contact' => $request->contact_num
                ], 201);
            } catch (\Illuminate\Validation\ValidationException $e) {
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
        $by = request()->query('by');
        $stff = request()->query('stff');

        $application = ApplicationForm::query()
            ->where($by, $value)
            ->when($by === 'id', fn($q) => $q->with(['transactions.motorcycle', 'address', 'ciReport']))
            ->when($stff === 'record_id', fn($q) => $q->with(['transactions.motorcycle']))
            ->first();

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
            $updateData = ['apply_status' => $request->apply_status];

            switch ($request->apply_status) {
                case 'accepted':
                    $updateData += [
                        'ci_id' => $request->ci_id,
                        'from_sched' => $request->from_sched,
                        'to_sched' => $request->to_sched,
                    ];
                    break;
                case 'denied':
                case 'declined':
                    $updateData['reject_reason'] = $request->message;
                    break;
                case 'approved':
                    $dueDate = Carbon::parse($request->due_date);
                    $schedules = collect(range(1, $request->tenure))->map(function ($month) use ($request, $application, $dueDate) {
                        return [
                            'application_form_id' => $application->id,
                            'due_date' => $dueDate->copy()->addMonths($month),
                            'amount_due' => $request->emi,
                            'status' => 'pending'
                        ];
                    });
                    $application->schedules()->createMany($schedules);
                    break;
            }

            $application->update($updateData);
            $application->notify(new ApplicationStatus([
                'status' => $request->apply_status,
                'recordID' => $application->record_id,
                'type' => $request->type,
                'message' => $request->message,
                'resubmit' => $request->resubmit
            ]));

            return response()->json([
                'message' => 'Application updated successfully',
                'type' => 'success',
                'data' => $application
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
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

    public function count(Request $request)
    {
        $data = [];
        $type = $request->input('type');
        $month = $request->input('month');

        if ($request->boolean('analysis'))
            $data = ApplicationForm::select('apply_status', 'created_at')->get();

        if ($month) {
            try {
                $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid month format. Use YYYY-MM.'], 400);
            }
        } else {
            $date = Carbon::now()->startOfMonth();
        }

        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $prevStart = $date->copy()->subMonth()->startOfMonth();
        $prevEnd = $date->copy()->subMonth()->endOfMonth();

        $currentQuery = ApplicationForm::whereBetween('created_at', [$startDate, $endDate]);
        $previousQuery = ApplicationForm::whereBetween('created_at', [$prevStart, $prevEnd]);

        if ($type) {
            $currentQuery->where('apply_status', $type);
            $previousQuery->where('apply_status', $type);

            $currentCount = $currentQuery->count();
            $previousCount = $previousQuery->count();

            $difference = $currentCount - $previousCount;
            $diffLabel = $difference > 0 ? '+' . $difference : (string)$difference;

            return response()->json([
                'month' => $date->format('F Y'),
                'count' => $currentCount,
                'difference' => $diffLabel,
                'message' => "{$diffLabel} since last month"
            ]);
        }

        $types = ['pending', 'accepted', 'denied', 'evaluated', 'approved', 'declined', 'cancelled', 'paid'];
        $results = [];

        foreach ($types as $t) {
            $current = ApplicationForm::where('apply_status', $t)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $previous = ApplicationForm::where('apply_status', $t)
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->count();

            $diff = $current - $previous;
            $results[$t] = [
                'count' => $current,
                'difference' => $diff >= 0 ? '+' . $diff : (string)$diff,
                'increment_type' => $diff > 0 ? 'incremented' : ($diff < 0 ? 'decremented' : 'neutral'),
            ];
        }

        $totalCurrent = collect($results)->sum('count');
        $totalPrevious = ApplicationForm::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $totalDiff = $totalCurrent - $totalPrevious;

        return response()->json([
            'data' => $data,
            'month' => $date->format('F Y'),
            'pending' => $results['pending'],
            'accepted' => $results['accepted'],
            'denied' => $results['denied'],
            'evaluated' => $results['evaluated'],
            'approved' => $results['approved'],
            'declined' => $results['declined'],
            'paid' => $results['paid'],
            'total' => [
                'count' => $totalCurrent,
                'difference' => $totalDiff >= 0 ? '+' . $totalDiff : (string)$totalDiff,
                'increment_type' => $totalDiff > 0 ? 'incremented' : ($totalDiff < 0 ? 'decremented' : 'neutral'),
            ],
        ]);
    }
}
