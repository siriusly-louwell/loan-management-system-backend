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

        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'brand' => 'required|string',
                'unit_type' => 'required|string',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'interest' => 'required|integer',
                'tenure' => 'required|integer',
                'rebate' => 'required|numeric',
                'downpayment' => 'required|numeric',
                'colors' => 'required|array',
                'colors.*.hex_value' => 'required|string',
                'colors.*.quantity' => 'required|integer',
                'colors.*.images' => 'array',
                'colors.*.images.*' => 'file|mimes:jpg,jpeg,png',
            ]);
            $totalQuantity = 1;
            foreach ($validated["colors"] as $colorGroup) {
                $totalQuantity += $colorGroup["quantity"];
            }
            $path = $validated["colors"][0]["images"][0]->store('uploads', 'public');
            $images[] = $path;
            $motor = Motorcycle::create([
                'name' => $validated['name'],
                'brand' => $validated['brand'],
                'unit_type' => $validated['unit_type'],
                'price' => $validated['price'],
                'description' => $validated['description'],
                'quantity' => $totalQuantity,
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

            foreach ($request->colors as $index => $colorGroup) {
                // Save each color entry
                $color = $motor->colors()->create([
                    "motorcycle_id" => $motor->id,
                    'hex_value' => $colorGroup['hex_value'],
                    'quantity' => $colorGroup['quantity'],
                ]);

                // Save per-color images
                if ($request->hasFile("colors.$index.images")) {
                    foreach ($request->file("colors.$index.images") as $image) {
                        $path = $image->store('uploads', 'public');

                        $motor->images()->create([
                            'path' => $path,
                            'image_type' => 'color',
                            'motorcycle_id' => $motor->id, // if you want to associate images per color
                            "color_id" => $color->id
                        ]);
                    }
                }
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
        $motorcycle->load(['colors.images']);
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
        Log::info("UPDATE REQUEST RECEIVED", [
            'request_all' => $request->all()
        ]);

        try {
            /* ------------------------------------------------------
        | 1. Validate Request
        ------------------------------------------------------ */
            $validated = $request->validate([
                'name' => 'required|string',
                'brand' => 'required|string',
                'unit_type' => 'required|string',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'interest' => 'required|integer',
                'tenure' => 'required|integer',
                'rebate' => 'required|numeric',
                'downpayment' => 'required|numeric',
                'colors' => 'required|array',
                'colors.*.hex_value' => 'required|string',
                'colors.*.quantity' => 'required|integer',
                'colors.*.images' => 'array',
                'colors.*.images.*' => 'file|mimes:jpg,jpeg,png',
            ]);

            Log::info("VALIDATION PASSED", $validated);

            /* ------------------------------------------------------
        | 2. DELETE OLD IMAGES
        ------------------------------------------------------ */
            if ($request->has('imagesToDelete')) {
                foreach ($request->imagesToDelete as $id) {
                    $img = Image::where('id', $id)
                        ->where('motorcycle_id', $motorcycle->id)
                        ->first();

                    if ($img) {
                        if (Storage::disk('public')->exists($img->path)) {
                            Storage::disk('public')->delete($img->path);
                        }

                        $img->delete();

                        Log::info("IMAGE DELETED", ['id' => $id]);
                    }
                }
            }

            /* ------------------------------------------------------
        | 3. UPDATE OR CREATE COLORS + UPLOAD NEW IMAGES
        ------------------------------------------------------ */
            if ($request->has('colors')) {
                foreach ($request->colors as $index => $c) {
                    // 3A. UPDATE EXISTING COLOR
                    if (isset($c['id'])) {
                        $colorRecord = $motorcycle->colors()->find($c['id']);
                        if ($colorRecord) {
                            $colorRecord->update([
                                'hex_value' => $c['hex_value'],
                                'quantity'  => $c['quantity'],
                            ]);

                            Log::info("COLOR UPDATED", [
                                'color_id' => $c['id'],
                                'data' => $c
                            ]);
                        }
                    }

                    // 3B. CREATE NEW COLOR
                    else {
                        $colorRecord = $motorcycle->colors()->create([
                            'hex_value' => $c['hex_value'],
                            'quantity'  => $c['quantity'],
                        ]);

                        Log::info("COLOR CREATED", [
                            'data' => $c,
                            'new_id' => $colorRecord->id
                        ]);
                    }

                    // 3C. STORE NEW IMAGES UNDER THIS COLOR
                    if ($request->hasFile("colors.$index.images")) {
                        foreach ($request->file("colors.$index.images") as $file) {
                            $path = $file->store('uploads', 'public');

                            $motorcycle->images()->create([
                                'path' => $path,
                                'image_type' => 'color',
                                'color_id' => $colorRecord->id
                            ]);

                            Log::info("NEW IMAGE SAVED", [
                                'path' => $path,
                                'color_id' => $colorRecord->id
                            ]);
                        }
                    }
                }
            }

            /* ------------------------------------------------------
        | 4. UPDATE MOTORCYCLE MAIN FIELDS
        ------------------------------------------------------ */
            $motorcycle->fill($validated)->save();
            Log::info("MOTORCYCLE UPDATED", ['fields' => $validated]);

            /* ------------------------------------------------------
        | 5. UPDATE THUMBNAIL (file_path)
        ------------------------------------------------------ */
            $firstImage = $motorcycle->images()->first();

            $motorcycle->file_path = $firstImage
                ? $firstImage->path
                : "motor_icon";

            $motorcycle->save();

            Log::info("THUMBNAIL UPDATED", ['file_path' => $motorcycle->file_path]);

            return response()->json([
                'message' => 'Unit updated successfully!',
                'type' => 'success',
                'updated_fields' => $validated,
                'deleted_images' => $request->imagesToDelete ?? [],
            ], 200);
        } catch (\Exception $e) {

            Log::error("UPDATE FAILED", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Update failed',
                'type' => 'error',
                'error' => $e->getMessage(),
            ], 500);
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
        $data = [];
        $type = $request->input('type');
        $month = $request->input('month');

        if ($request->boolean('analysis'))
            $data = Motorcycle::select('brand', 'unit_type', 'price', 'created_at')->get();

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
        $currentQuery = Motorcycle::whereBetween('created_at', [$startDate, $endDate]);
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
                'count' => $currentCount,
                'difference' => $diffLabel,
                'message' => "{$diffLabel} since last month"
            ]);
        }

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
                'difference' => $diff >= 0 ? '+' . $diff : (string)$diff,
                'increment_type' => $diff > 0 ? 'incremented' : ($diff < 0 ? 'decremented' : 'neutral'),
            ];
        }

        $brands = [
            'Honda',
            'Yamaha',
            'Kawasaki',
            'Suzuki',
            'KTM',
            'Kymco',
            'SYM',
            'Skygo',
            'Bennelli',
            'Bristol',
            'Rusi',
            'Motorstar',
            'QJMotor',
            'FKM'
        ];
        $brandResults = [];

        foreach ($brands as $t) {
            $current = Motorcycle::where('brand', $t)->count();
            $brandResults[$t] = [
                'count' => $current,
            ];
        }

        $totalCurrent = $results['new']['count'] + $results['repo']['count'];
        $totalPrevious = Motorcycle::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $totalDiff = $totalCurrent - $totalPrevious;

        return response()->json([
            'data' => $data,
            'brand_count' => $brandResults,
            'new' => $results['new'],
            'repo' =>  $results['repo'],
            'total' => [
                'count' => $totalCurrent,
                'difference' => $totalDiff >= 0 ? '+' . $totalDiff : (string)$totalDiff,
                'increment_type' => $totalDiff > 0 ? 'incremented' : ($totalDiff < 0 ? 'decremented' : 'neutral'),
            ],
        ]);
    }
}
