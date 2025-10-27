<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Http\Controllers\Controller;
use App\Models\ApplicationForm;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $payments = Payment::with('application')->get();
        // return response()->json($payments);

        $perPage = $request->input('per_page', 10);
        $credits = Payment::with(['application']);

        if ($request->has('customer')) {
            $customer = $request->input('customer');

            $credits->when($customer, function ($query, $customer) {
                $query->where('user_id', $customer);
            });
        }

        // if ($request->has('search')) {
        //     $search = $request->input('search');

        //     $credits->when($search, function ($query, $search) {
        //         $query->where(function ($q) use ($search) {
        //             $q->where('status', 'like', "%{$search}%")
        //                 ->orWhere('amount', 'like', "%{$search}%");
        //         });
        //     });
        // }

        if ($request->has('min') || $request->has('max')) {
            $min = $request->input('min');
            $max = $request->input('max');
            $type = $request->input('type');

            $credits->when($min, fn($q) => $q->where($type, '>=', $min))
                ->when($max, fn($q) => $q->where($type, '<=', $max));
        }

        return response()->json($credits->orderBy('created_at', 'desc')->paginate($perPage));
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
                'amount_paid' => 'required|numeric',
                'application_form_id' => 'required|integer'
            ]);

            $this->validatePayment($validated['amount_paid'], $request->application_form_id);
            $schedule = Schedule::select('id', 'amount_due', 'status', 'due_date')
                ->where('application_form_id', $validated['application_form_id'])
                ->where('status', 'pending')
                ->orderBy('due_date', 'asc')
                ->first();

            if (!$schedule)
                return response()->json([
                    'message' => 'No pending schedule found for this application',
                    'type' => 'error'
                ], 404);

            $paymentStatus = $this->determinePaymentStatus($schedule->due_date);

            // ? Handle payment distribution if amount exceeds schedule
            if ($validated['amount_paid'] > $schedule->amount_due) {
                $payments = $this->distributePayment(
                    $validated['application_form_id'],
                    $validated['amount_paid']
                );

                foreach ($payments as $paymentData) {
                    Payment::create(array_merge($paymentData, [
                        'status' => $paymentStatus,
                        'cert_num' => $request->cert_num,
                        'issued_at' => $request->issued_at
                    ]));
                }
            } else {
                // ? Handle single payment
                $totalPreviousPayments = Payment::where('application_form_id', $validated['application_form_id'])
                    ->where('schedule_id', $schedule->id)
                    ->sum('amount_paid');

                Payment::create([
                    'application_form_id' => $validated['application_form_id'],
                    'schedule_id' => $schedule->id,
                    'cert_num' => $request->cert_num,
                    'issued_at' => $request->issued_at,
                    'amount_paid' => $validated['amount_paid'],
                    'status' => $paymentStatus
                ]);

                if (($totalPreviousPayments + $validated['amount_paid']) >= $schedule->amount_due) {
                    $schedule->status = 'paid';
                    $schedule->save();
                }
            }

            $this->updateApplicationStatus($validated['application_form_id']);
            return response()->json([
                'message' => 'Payment saved successfully!',
                'type' => 'success'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing payment',
                'type' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show($value)
    {
        $by = request()->query('by');
        $isLatest = filter_var(request()->query('isLatest'), FILTER_VALIDATE_BOOLEAN);

        $query = Payment::where($by, $value);

        if ($isLatest) {
            $payment = $query
                ->orderByDesc('created_at')
                ->orderByDesc('schedule_id')
                ->first();
        } else {
            $payment = $query->first();
        }

        return response()->json($payment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        //
    }

    public function count(Request $request)
    {
        $data = [];
        $type = $request->input('type');
        $month = $request->input('month');

        if ($request->boolean('analysis'))
            $data = Payment::select('status', 'created_at')->get();

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

        $currentQuery = Payment::whereBetween('created_at', [$startDate, $endDate]);
        $previousQuery = Payment::whereBetween('created_at', [$prevStart, $prevEnd]);

        if ($type) {
            $currentQuery->where('status', $type);
            $previousQuery->where('status', $type);

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

        $types = ['on_time', 'late'];
        $results = [];

        foreach ($types as $t) {
            $current = Payment::where('status', $t)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $previous = Payment::where('status', $t)
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->count();

            $diff = $current - $previous;
            $results[$t] = [
                'count' => $current,
                'difference' => $diff >= 0 ? '+' . $diff : (string)$diff,
                'increment_type' => $diff > 0 ? 'incremented' : ($diff < 0 ? 'decremented' : 'neutral'),
            ];
        }

        $totalCurrent = $results['on_time']['count'] + $results['late']['count'];
        $totalPrevious = Payment::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $totalDiff = $totalCurrent - $totalPrevious;

        return response()->json([
            'data' => $data,
            'month' => $date->format('F Y'),
            'on_time' => $results['on_time'],
            'late' => $results['late'],
            'total' => [
                'count' => $totalCurrent,
                'difference' => $totalDiff >= 0 ? '+' . $totalDiff : (string)$totalDiff,
                'increment_type' => $totalDiff > 0 ? 'incremented' : ($totalDiff < 0 ? 'decremented' : 'neutral'),
            ],
        ]);
    }

    private function validatePayment($amount, $applicationId)
    {
        // Check if amount is positive
        if ($amount <= 0) {
            throw new \Exception('Payment amount must be positive');
        }

        // Check if total payments don't exceed total loan amount
        $totalLoanAmount = Schedule::where('application_form_id', $applicationId)
            ->sum('amount_due');
        $totalPaid = Payment::where('application_form_id', $applicationId)
            ->sum('amount_paid');

        if (($totalPaid + $amount) > $totalLoanAmount) {
            throw new \Exception('Payment exceeds total loan amount');
        }
    }

    private function determinePaymentStatus($dueDate, $gracePeriodDays = 3)
    {
        $currentDate = Carbon::now();
        $dueDate = Carbon::parse($dueDate);
        $graceDate = $dueDate->copy()->addDays($gracePeriodDays);

        if ($currentDate->isAfter($graceDate)) {
            return 'late';
        }
        return 'on_time';
    }

    private function updateApplicationStatus($applicationId)
    {
        $totalSchedules = Schedule::where('application_form_id', $applicationId)->count();
        $paidSchedules = Schedule::where('application_form_id', $applicationId)
            ->where('status', 'paid')
            ->count();

        $status = ($totalSchedules === $paidSchedules) ? 'paid' : 'incomplete';

        ApplicationForm::where('id', $applicationId)
            ->update(['apply_status' => $status]);
    }

    private function distributePayment($applicationId, $amount)
    {
        $remainingAmount = $amount;
        $payments = [];

        while ($remainingAmount > 0) {
            $nextSchedule = Schedule::where('application_form_id', $applicationId)
                ->where('status', 'pending')
                ->orderBy('due_date', 'asc')
                ->first();

            if (!$nextSchedule) {
                // ? Store excess as credit or handle according to business rules
                break;
            }

            $paymentAmount = min($remainingAmount, $nextSchedule->amount_due);
            $payments[] = [
                'schedule_id' => $nextSchedule->id,
                'amount_paid' => $paymentAmount,
                'application_form_id' => $applicationId
            ];

            $remainingAmount -= $paymentAmount;
            if ($paymentAmount >= $nextSchedule->amount_due) {
                $nextSchedule->status = 'paid';
                $nextSchedule->save();
            }
        }

        return $payments;
    }
}
