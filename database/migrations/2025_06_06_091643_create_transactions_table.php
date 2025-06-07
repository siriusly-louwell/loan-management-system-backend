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
            $table->foreignId('application_id')->nullable()->constrained('application_forms')->onDelete('SET NULL');
            $table->unsignedBigInteger('motorcycle_id');
            $table->foreign('motorcycle_id')->references('id')->on('motorcycles');
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
