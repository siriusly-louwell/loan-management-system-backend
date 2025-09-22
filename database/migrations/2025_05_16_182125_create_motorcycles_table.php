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
            $table->string("unit_type");
            $table->text('description');
            $table->decimal('price', 20, 2);
            $table->integer('quantity');
            $table->text('file_path');
            $table->integer('interest');
            $table->decimal('rebate', 20, 2);
            $table->integer('tenure');
            $table->decimal('downpayment', 20, 2);
            $table->string('engine')->nullable();
            $table->string('compression')->nullable();
            $table->string('displacement')->nullable();
            $table->string('horsepower')->nullable();
            $table->string('torque')->nullable();
            $table->string('fuel')->nullable();
            $table->string('drive')->nullable();
            $table->string('transmission')->nullable();
            $table->string('cooling')->nullable();
            $table->string('front_suspension')->nullable();
            $table->string('rear_suspension')->nullable();
            $table->string('frame')->nullable();
            $table->string('travel')->nullable();
            $table->string('swingarm')->nullable();
            $table->string('dry_weight')->nullable();
            $table->string('wet_weight')->nullable();
            $table->string('seat')->nullable();
            $table->string('wheelbase')->nullable();
            $table->string('fuel_tank')->nullable();
            $table->string('clearance')->nullable();
            $table->string('tires')->nullable();
            $table->string('wheel')->nullable();
            $table->string('brakes')->nullable();
            $table->string('abs')->nullable();
            $table->string('traction')->nullable();
            $table->string('tft')->nullable();
            $table->string('lighting')->nullable();
            $table->string('ride_mode')->nullable();
            $table->string('quickshifter')->nullable();
            $table->string('cruise')->nullable();
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
