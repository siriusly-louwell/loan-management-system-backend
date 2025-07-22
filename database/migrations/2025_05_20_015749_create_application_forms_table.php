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
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('SET NULL');
            $table->foreignId('ci_id')->nullable()->constrained('users')->onDelete('SET NULL');
            $table->string('record_id');
            $table->string('apply_status');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('contact_num', 15);
            $table->string('email')->nullable();
            $table->string('gender');
            $table->string('status');
            $table->string('educ_attain');
            $table->string('residence');
            $table->decimal('amortization', 20, 2)->nullable();
            $table->decimal('rent', 20, 2)->nullable();
            $table->string('sss')->nullable();
            $table->string('tin')->nullable();
            $table->text('birth_day');
            $table->string('birth_place', 100);
            $table->string('father_first', 30);
            $table->string('father_middle', 20);
            $table->string('father_last', 30);
            $table->string('mother_first', 30);
            $table->string('mother_middle', 20);
            $table->string('mother_last', 30);
            $table->text('comm_standing');
            $table->string('home_description');
            $table->string('income');
            $table->string('superior')->nullable();
            $table->string('employment_status')->nullable();
            $table->integer('yrs_in_service')->nullable();
            $table->decimal('rate', 30, 2);
            $table->string('employer')->nullable();
            $table->decimal('salary', 30, 2);
            $table->string('business');
            $table->decimal('living_exp', 20, 2);
            $table->decimal('rental_exp', 20, 2);
            $table->decimal('education_exp', 20, 2);
            $table->decimal('transportation', 20, 2);
            $table->decimal('insurance', 20, 2);
            $table->decimal('bills', 20, 2);
            $table->string('spouse_name')->nullable();
            $table->date('b_date')->nullable();
            $table->string('spouse_work')->nullable();
            $table->integer('children_num')->nullable();
            $table->integer('children_dep')->nullable();
            $table->string('school')->nullable();
            $table->text('id_pic');
            $table->text('valid_id');
            $table->text('residence_proof');
            $table->text('income_proof');
            $table->text('sketch');
            $table->date('from_sched')->nullable();
            $table->date('to_sched')->nullable();
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