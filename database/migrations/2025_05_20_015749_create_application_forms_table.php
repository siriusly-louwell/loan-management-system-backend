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
        Schema::create('application_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('address_id');
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');
            $table->string('gender');
            $table->string('status');
            $table->string('educ_attain');
            $table->string('residence');
            $table->decimal('amortization', 10, 2);
            $table->decimal('rent', 10, 2);
            $table->string('sss');
            $table->string('tin');
            $table->string('income');
            $table->string('superior');
            $table->string('employment_status');
            $table->integer('yrs_in_service');
            $table->string('rate');
            $table->string('employer');
            $table->string('salary');
            $table->string('business');
            $table->string('living_exp');
            $table->string('rental_exp');
            $table->string('education_exp');
            $table->string('transportation');
            $table->string('insurance');
            $table->string('bills');
            $table->string('spouse_name');
            $table->date('b_date');
            $table->string('spouse_work');
            $table->integer('children_num');
            $table->integer('children_dep');
            $table->string('school');
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
        Schema::dropIfExists('application_forms');
    }
};
