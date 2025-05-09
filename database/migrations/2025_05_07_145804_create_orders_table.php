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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->constrained('user_addresses')->onDelete('restrict');
            $table->string('courier');
            $table->string('service');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_fee', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_status')->default('pending'); // pending, paid, failed, expired
            $table->string('status')->default('pending'); // pending, processing, shipped, delivered, cancelled
            $table->string('external_id')->unique();
            $table->string('invoice_id')->nullable();
            $table->string('payment_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};