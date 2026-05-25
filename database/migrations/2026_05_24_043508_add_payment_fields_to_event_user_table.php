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
        Schema::table('event_user', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('user_id');
            $table->string('payment_status', 20)->default('free')->after('status');
            $table->decimal('amount', 10, 2)->default(0.00)->after('payment_status');
            $table->string('external_id', 50)->nullable()->after('amount');
            $table->string('invoice_id', 50)->nullable()->after('external_id');
            $table->string('payment_url', 255)->nullable()->after('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_user', function (Blueprint $table) {
            $table->dropColumn(['status', 'payment_status', 'amount', 'external_id', 'invoice_id', 'payment_url']);
        });
    }
};
