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
        Schema::create('ci_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_form_id');
            $table->foreign('application_form_id')->references('id')->on('application_forms');
            // $table->text('birth_day');
            // $table->string('birth_place', 100);
            // $table->string('father_first', 30);
            // $table->string('father_middle', 20);
            // $table->string('father_last', 30);
            // $table->string('mother_first', 30);
            // $table->string('mother_middle', 20);
            // $table->string('mother_last', 30);
            // $table->text('comm_standing');
            // $table->string('home_description');
            $table->text('recommendation', 12);
            $table->text('remarks')->nullable();
            $table->string('first_unit', 30)->nullable();
            // $table->text('sketch');
            $table->string('delivered', 5)->nullable();
            $table->string('outlet', 50)->nullable();
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
        Schema::dropIfExists('ci_reports');
    }
};
