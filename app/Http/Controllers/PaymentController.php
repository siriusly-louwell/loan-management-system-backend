<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payments = Payment::with('application')->get();
        return response()->json($payments);
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
                'amount_paid' => 'required|integer'
            ]);

            $motor = Payment::create([
                'application_form_id' => $request->application_form_id,
                'cert_num' => $request->cert_num,
                'issued_at' => $request->issued_at,
                'prev_balance' => $request->prev_balance,
                'curr_balance' => $request->prev_balance - $validated['amount_paid'],
                'amount_paid' => $validated['amount_paid'],
                'status' => $request->status
            ]);

            return response()->json(['message' => 'Payment saved successfully!', 'type' => 'success'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors ' => $e->errors()], 422);
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
        // $payment = Payment::where('application_form_id', $value)->get()->first();
        // return response()->json($payment);

        $by = request()->query('by');
        $application = Payment::where($by, $value)->first();

        return response()->json($application);
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
}
