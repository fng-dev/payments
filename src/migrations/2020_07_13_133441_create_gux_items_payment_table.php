<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuxItemsPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gux_items_payment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('price_unit', 10, 2)->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('img_url')->nullable();
            $table->unsignedBigInteger('payment_id');
            $table->foreign('payment_id')->references('id')->on('gux_payments')->onDelete('cascade');
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
        Schema::dropIfExists('gux_items_payment');
    }
}
