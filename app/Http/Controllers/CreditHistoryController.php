<?php

namespace App\Http\Controllers;

use App\Models\CreditHistory;
use Illuminate\Http\Request;

class CreditHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $credits = CreditHistory::query();

        if ($request->has('customer')) {
            $customer = $request->input('customer');

            $credits->when($customer, function ($query, $customer) {
                $query->where('user_id', $customer);
            });
        }

        if ($request->has('search')) {
            $search = $request->input('search');

            $credits->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('status', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%");
                });
            });
        }

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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CreditHistory  $creditHistory
     * @return \Illuminate\Http\Response
     */
    public function show(CreditHistory $creditHistory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CreditHistory  $creditHistory
     * @return \Illuminate\Http\Response
     */
    public function edit(CreditHistory $creditHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CreditHistory  $creditHistory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CreditHistory $creditHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CreditHistory  $creditHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(CreditHistory $creditHistory)
    {
        //
    }

    public function score(Request $request)
    {
        $history = CreditHistory::where('user_id', $request->id)->get();

        if ($history->isEmpty()) {
            return response()->json([
                'user_id' => $request->id,
                'score' => 100,
                'message' => 'No credit history found. Default score applied.'
            ]);
        }

        $missed = $history->where('status', 'defaulted')->count();
        $late = $history->where('status', 'late')->count();
        $totalLoans = $history->count();

        $score = 100 - ($missed * 30) - ($late * 10);
        // ? clamp between 0–100
        $score = max(0, min(100, $score));

        return response()->json([
            'total_loans' => $totalLoans,
            'late_payments' => $late,
            'defaulted_loans' => $missed,
            'score' => $score,
        ]);
    }
}
