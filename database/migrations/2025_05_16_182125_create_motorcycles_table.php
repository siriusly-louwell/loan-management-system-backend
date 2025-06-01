<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('motorcycles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand');
            $table->text('description');
            $table->decimal('price', 20, 2);
            $table->integer('quantity');
            $table->text('file_path');
            $table->integer('interest');
            $table->decimal('rebate', 20, 2);
            $table->integer('tenure');
            $table->decimal('downpayment', 20, 2);
            $table->string('engine');
            $table->string('compression');
            $table->string('displacement');
            $table->string('horsepower');
            $table->string('torque');
            $table->string('fuel');
            $table->string('drive');
            $table->string('transmission');
            $table->string('cooling');
            $table->string('front_suspension');
            $table->string('rear_suspension');
            $table->string('frame');
            $table->string('travel');
            $table->string('swingarm');
            $table->string('dry_weight');
            $table->string('wet_weight');
            $table->string('seat');
            $table->string('wheelbase');
            $table->string('fuel_tank');
            $table->string('clearance');
            $table->string('tires');
            $table->string('wheel');
            $table->string('brakes');
            $table->string('abs');
            $table->string('traction');
            $table->string('tft');
            $table->string('lighting');
            $table->string('ride_mode');
            $table->string('quickshifter');
            $table->string('cruise');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('motorcycles');
    }
};
