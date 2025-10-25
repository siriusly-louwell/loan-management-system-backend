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

    public function creditScore(Request $request)
    {
        $history = CreditHistory::where('user_id', $request->id)->get();

        if ($history->isEmpty()) {
            return response()->json([
                'user_id' => $request->id,
                'credit_score' => 100,
                'message' => 'No credit history found. Default score applied.'
            ]);
        }

        $missed = $history->where('status', 'defaulted')->count();
        $late = $history->where('status', 'late')->count();
        $totalLoans = $history->count();

        $score = 100 - ($missed * 30) - ($late * 10);
        $score = max(0, min(100, $score)); // clamp between 0–100

        return response()->json([
            'user_id' => $request->id,
            'total_loans' => $totalLoans,
            'late_payments' => $late,
            'defaulted_loans' => $missed,
            'credit_score' => $score,
        ]);
    }
}
