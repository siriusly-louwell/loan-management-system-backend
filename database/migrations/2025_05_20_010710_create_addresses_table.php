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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->text('personal_pres');
            $table->text('personal_prev');
            $table->text('parent_pres');
            $table->text('parent_prev');
            $table->text('spouse_pres')->nullable();
            $table->text('spouse_prev')->nullable();
            $table->text('comaker_pres')->nullable();
            $table->text('comaker_perm')->nullable();
            $table->text('sp_parent_pres')->nullable();
            $table->text('sp_parent_prev')->nullable();
            $table->text('employer_address')->nullable();
            $table->text('comaker_emp_address')->nullable();
            $table->text('spouse_emp_address')->nullable();
            $table->text('lat')->nullable();
            $table->text('lng')->nullable();
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
        Schema::dropIfExists('addresses');
    }
};
