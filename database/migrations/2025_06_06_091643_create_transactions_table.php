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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_form_id');
            $table->foreign('application_form_id')->references('id')->on('application_forms');
            $table->foreignId('motorcycle_id')->nullable()->constrained('motorcycles')->onDelete('SET NULL');
            $table->string('color');
            $table->integer('tenure');
            $table->decimal('downpayment', 20, 2);
            $table->integer('quantity');
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
        Schema::dropIfExists('transactions');
    }
};
