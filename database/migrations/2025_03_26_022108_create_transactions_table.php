<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('currency_id');
            $table->string('payment_system'); // 'easy_money' or 'super_walletz'
            $table->string('status'); // 'pending', 'success', 'failed'
            $table->string('transaction_id')->nullable(); // External payment system ID
            $table->text('metadata')->nullable(); // Additional data
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('currency_id')->references('id')->on('currencies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
