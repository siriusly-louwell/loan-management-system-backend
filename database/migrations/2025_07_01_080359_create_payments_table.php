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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_form_id');
            $table->foreign('application_form_id')->references('id')->on('application_forms');
            $table->string('cert_num');
            $table->text('issued_at');
            $table->decimal('prev_balance', 20, 2);
            $table->decimal('amount_paid', 20, 0);
            $table->decimal('curr_balance', 20, 0);
            $table->string('status');
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
        Schema::dropIfExists('payments');
    }
};
