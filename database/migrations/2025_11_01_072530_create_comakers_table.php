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
        Schema::create('comakers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_form_id');
            $table->foreign('application_form_id')->references('id')->on('application_forms');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('contact_num', 15)->nullable();
            $table->string('facebook')->nullable();
            $table->string('email')->nullable();
            $table->date('birth_day')->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->string('gender')->nullable();
            $table->string('status')->nullable();
            $table->string('religion')->nullable();
            $table->string('tribe')->nullable();
            $table->string('educ_attain')->nullable();
            $table->string('citizenship')->nullable();
            $table->string('occupation')->nullable();
            $table->integer('yrs_in_service')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('employer')->nullable();
            $table->string('spouse_first', 30)->nullable();
            $table->string('spouse_middle', 20)->nullable();
            $table->string('spouse_last', 30)->nullable();
            $table->string('sp_citizenship')->nullable();
            $table->string('sp_occupation')->nullable();
            $table->integer('sp_yrs_in_service')->nullable();
            $table->string('sp_emp_status')->nullable();
            $table->string('sp_employer')->nullable();
            $table->string('sp_father_first', 30)->nullable();
            $table->string('sp_father_middle', 20)->nullable();
            $table->string('sp_father_last', 30)->nullable();
            $table->string('sp_mother_first', 30)->nullable();
            $table->string('sp_mother_middle', 20)->nullable();
            $table->string('sp_mother_last', 30)->nullable();
            $table->text('co_sketch')->nullable();
            $table->text('co_valid_id')->nullable();
            $table->text('co_id_pic')->nullable();
            $table->text('co_residence_proof')->nullable();
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
        Schema::dropIfExists('comakers');
    }
};
