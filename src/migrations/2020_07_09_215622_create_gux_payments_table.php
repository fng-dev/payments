<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuxPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gux_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('buy_order')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('shipping_amount', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('payment_company')->nullable()->comment('WebPay / MercadoPago');
            $table->string('session_id')->nullable()->comment('Session transaction id');
            $table->string('payment_id')->nullable()->comment('External Reference');
            $table->integer('share_number')->nullable()->comment('Quotas');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('gux_payments');
    }
}
