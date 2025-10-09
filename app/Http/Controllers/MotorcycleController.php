<?php

namespace App\Http\Controllers;

use App\Models\Motorcycle;
use App\Models\Image;
use Hamcrest\Arrays\IsArray;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MotorcycleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($request->has('ndi')) {
            $ndi = $request->input('ndi');

            $motorcycles = Motorcycle::with(['colors', 'images'])->get()
                ->filter(function ($motor) use ($ndi) {
                    $tenure = $motor->tenure * 12;
                    $loanAmount = ($motor->price ?? 0) - ($motor->downpayment ?? 0);
                    $monthlyRate = $motor->interest / 12 / 100;

                    if ($tenure <= 0 || $loanAmount <= 0) {
                        return false;
                    }

                    $emi = $monthlyRate == 0
                        ? $loanAmount / $tenure
                        : ($loanAmount * $monthlyRate * pow(1 + $monthlyRate, $tenure)) /
                        (pow(1 + $monthlyRate, $tenure) - 1);

                    return round($emi, 2) / $ndi <= 0.3;
                });

            return response()->json($motorcycles->values());
        }


        $perPage = $request->input('per_page', 8);
        $motorcycles = Motorcycle::with(['colors', 'images']);

        if ($request->has('unit_type')) {
            $unitType = $request->input('unit_type');

            $motorcycles->when($unitType, function ($query, $unitType) {
                $query->where('unit_type', $unitType);
            });
        }

        if ($request->has('search')) {
            $search = $request->input('search');

            $motorcycles->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('interest', 'like', "%{$search}%")
                        ->orWhere('rebate', 'like', "%{$search}%")
                        ->orWhere('tenure', 'like', "%{$search}%")
                        ->orWhere('price', 'like', "%{$search}%");
                });
            });
        }

        if ($request->has('min') || $request->has('max')) {
            $min = $request->input('min');
            $max = $request->input('max');
            $type = $request->input('type');

            $motorcycles->when($min, fn($q) => $q->where($type, '>=', $min))
                ->when($max, fn($q) => $q->where($type, '<=', $max));
        }

        return response()->json($motorcycles->orderBy('created_at', 'desc')->paginate($perPage));
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
        $images = [];
        $angles = [];

        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'brand' => 'required|string',
                'unit_type' => 'required|string',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'quantity' => 'required|array',
                'quantity.*' => 'required|integer',
                'totalQuantity' => 'required|integer',
                'interest' => 'required|integer',
                'tenure' => 'required|integer',
                'rebate' => 'required|numeric',
                'downpayment' => 'required|numeric',
                'files' => 'required|array',
                'files.*' => 'required|file|mimes:jpg,jpeg,png',
                'colors' => 'required|array',
                'colors.*' => 'required|string',
            ]);

            foreach ($request->file('files') as $file) {
                $path = $file->store('uploads', 'public');
                $images[] = $path;
            }

            foreach ($request->file('angles') as $file) {
                $path = $file->store('uploads', 'public');
                $angles[] = $path;
            }

            $motor = Motorcycle::create([
                'name' => $validated['name'],
                'brand' => $validated['brand'],
                'unit_type' => $validated['unit_type'],
                'price' => $validated['price'],
                'description' => $validated['description'],
                'quantity' => $validated['totalQuantity'],
                'rebate' => $validated['rebate'],
                'tenure' => $validated['tenure'],
                'interest' => $validated['interest'],
                'downpayment' => $validated['downpayment'],
                'file_path' => $images[0],
                'engine' => $request->engine,
                'compression' => $request->compression,
                'displacement' => $request->displacement,
                'horsepower' => $request->horsepower,
                'torque' => $request->torque,
                'fuel' => $request->fuel,
                'drive' => $request->drive,
                'transmission' => $request->transmission,
                'cooling' => $request->cooling,
                'front_suspension' => $request->front_suspension,
                'rear_suspension' => $request->rear_suspension,
                'frame' => $request->frame,
                'travel' => $request->travel,
                'swingarm' => $request->swingarm,
                'dry_weight' => $request->dry_weight,
                'wet_weight' => $request->wet_weight,
                'seat' => $request->seat,
                'wheelbase' => $request->wheelbase,
                'fuel_tank' => $request->fuel_tank,
                'clearance' => $request->clearance,
                'tires' => $request->tires,
                'wheel' => $request->wheel,
                'brakes' => $request->brakes,
                'abs' => $request->abs,
                'traction' => $request->traction,
                'tft' => $request->tft,
                'lighting' => $request->lighting,
                'ride_mode' => $request->ride_mode,
                'quickshifter' => $request->quickshifter,
                'cruise' => $request->cruise,
            ]);

            foreach ($validated['colors'] as $key =>  $color) {
                $motor->colors()->create(['color' => $color, 'quantity' => $request->quantity[$key]]);
            }

            foreach ($images as $path) {
                $motor->images()->create(['path' => $path, 'image_type' => 'color']);
            }

            foreach ($angles as $path) {
                $motor->images()->create(['path' => $path, 'image_type' => 'angle']);
            }

            return response()->json([
                'message' => 'Unit was created successfully!',
                'type' => 'success'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Failed to save data',
                'type' => 'error',
                'errors ' => $e->errors()
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
        $motorcycle->load(['colors', 'images']);
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
                'unit_type' => 'sometimes|string',
                'color' => 'sometimes|string',
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric',
                'quantity' => 'sometimes|integer',
                'totalQuantity' => 'sometimes|array',
                'totalQuantity.*' => 'sometimes|integer',
                'rebate' => 'sometimes|numeric',
                'downpayment' => 'sometimes|numeric',
                'interest' => 'sometimes|integer',
                'tenure' => 'sometimes|integer',
                'file_path' => 'sometimes|string',
                'engine' => 'sometimes|string',
                'compression' => 'sometimes|string',
                'displacement' => 'sometimes|string',
                'horsepower' => 'sometimes|string',
                'torque' => 'sometimes|string',
                'fuel' => 'sometimes|string',
                'drive' => 'sometimes|string',
                'transmission' => 'sometimes|string',
                'cooling' => 'sometimes|string',
                'front_suspension' => 'sometimes|string',
                'rear_suspension' => 'sometimes|string',
                'frame' => 'sometimes|string',
                'travel' => 'sometimes|string',
                'swingarm' => 'sometimes|string',
                'dry_weight' => 'sometimes|string',
                'wet_weight' => 'sometimes|string',
                'seat' => 'sometimes|string',
                'wheelbase' => 'sometimes|string',
                'fuel_tank' => 'sometimes|string',
                'clearance' => 'sometimes|string',
                'tires' => 'sometimes|string',
                'wheel' => 'sometimes|string',
                'brakes' => 'sometimes|string',
                'abs' => 'sometimes|string',
                'traction' => 'sometimes|string',
                'tft' => 'sometimes|string',
                'lighting' => 'sometimes|string',
                'ride_mode' => 'sometimes|string',
                'quickshifter' => 'sometimes|string',
                'cruise' => 'sometimes|string',
                'colors' => 'array',
                'colors.*' => 'sometimes|string'
            ]);

            if ($request->has('colors')) {
                foreach ($request->colors as $key => $color) {
                    $motorcycle->colors()->updateOrCreate(
                        ['color' => $color],
                        ['quantity' => $request->totalQuantity[$key]]
                    );
                }
            }

            if ($request->has('deletes')) {
                foreach ($request->deletes as $id) {
                    $image = Image::where('id', $id)
                        ->where('motorcycle_id', $motorcycle->id)
                        ->first();

                    if ($image) {
                        if ($image->path && Storage::disk('public')->exists($image->path))
                            Storage::disk('public')->delete($image->path);

                        $image->delete();
                    }

                    $motorImg = $motorcycle->images()->first();

                    if ($motorImg) $validatedData['file_path'] = $motorImg->path;
                    else $validatedData['file_path'] = 'motor_icon';
                }
            }

            if ($request->has('newColors')) {
                foreach ((array) $request->file('newColors') as $index => $imgData) {
                    $file = $request->file("newColors.$index");
                    $path = $file->store('uploads', 'public');

                    $motorcycle->images()->create(['path' => $path, 'image_type' => 'color']);

                    if ($index == 0) $validatedData['file_path'] = $path;
                }
            }

            if ($request->has('newAngles')) {
                foreach ((array) $request->file('newAngles') as $index => $imgData) {
                    $file = $request->file("newAngles.$index");
                    $path = $file->store('uploads', 'public');

                    $motorcycle->images()->create(['path' => $path, 'image_type' => 'angle']);

                    if ($index == 0) $validatedData['file_path'] = $path;
                }
            }

            $motorcycle->update($validatedData);

            return response()->json([
                'message' => 'Unit was updated successfully!',
                'type' => 'success'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
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

    public function count(Request $request)
    {
        $type = $request->input('type'); // 'new', 'repo', or null
        $month = $request->input('month'); // e.g. '2025-10'

        // Determine the target month
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

        // Define previous month range
        $prevStart = $date->copy()->subMonth()->startOfMonth();
        $prevEnd = $date->copy()->subMonth()->endOfMonth();

        // Base query for current month
        $currentQuery = Motorcycle::whereBetween('created_at', [$startDate, $endDate]);
        // Base query for previous month
        $previousQuery = Motorcycle::whereBetween('created_at', [$prevStart, $prevEnd]);

        if ($type) {
            $currentQuery->where('unit_type', $type);
            $previousQuery->where('unit_type', $type);

            $currentCount = $currentQuery->count();
            $previousCount = $previousQuery->count();

            $difference = $currentCount - $previousCount;
            $diffLabel = $difference > 0 ? '+' . $difference : (string)$difference;

            return response()->json([
                'month' => $date->format('F Y'),
                'type' => $type,
                'count' => $currentCount,
                'difference' => $diffLabel,
                'message' => "{$diffLabel} since last month"
            ]);
        }

        // If no type, compute both 'new' and 'repo' totals
        $types = ['new', 'repo'];
        $results = [];

        foreach ($types as $t) {
            $current = Motorcycle::where('unit_type', $t)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $previous = Motorcycle::where('unit_type', $t)
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->count();

            $diff = $current - $previous;
            $results[$t] = [
                'count' => $current,
                'difference' => $diff > 0 ? '+' . $diff : (string)$diff,
            ];
        }

        $totalCurrent = $results['new']['count'] + $results['repo']['count'];
        $totalPrevious = Motorcycle::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $totalDiff = $totalCurrent - $totalPrevious;

        return response()->json([
            'month' => $date->format('F Y'),
            'new' => $results['new'],
            'repo' => $results['repo'],
            'total' => [
                'count' => $totalCurrent,
                'difference' => $totalDiff > 0 ? '+' . $totalDiff : (string)$totalDiff,
                'increment_type' => $totalDiff > 0,
            ],
        ]);
    }
}
