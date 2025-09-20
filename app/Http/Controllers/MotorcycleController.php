<?php

namespace App\Http\Controllers;

use App\Models\Motorcycle;
use App\Models\Image;
use Hamcrest\Arrays\IsArray;
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
        // return response()->json(Motorcycle::all());

        // $motorcycles = $request->has('ids')
        //     ? Motorcycle::with('colors')->whereIn('id', $request->input('ids'))->get()
        //     : Motorcycle::with(['colors', 'images'])->get();

        // $motorcycles = Motorcycle::with(['colors', 'images'])->get();

        $perPage = $request->input('per_page', 8);
        $motorcycles = Motorcycle::with(['colors', 'images'])->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($motorcycles);
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
                'color' => 'sometimes|string',
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric',
                'quantity' => 'sometimes|integer',
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
                $motorcycle->colors()->delete();

                foreach ($request->colors as $color) {
                    $motorcycle->colors()->create(['color' => $color]);
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
}
