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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('email'); 
            $table->integer('age')->nullable()->after('phone_number'); 
            $table->string('occupation')->nullable()->after('age'); 
            $table->text('reason_for_joining')->nullable()->after('occupation'); 
            $table->boolean('consent')->default(false)->after('reason_for_joining'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_number',
                'age',
                'occupation',
                'reason_for_joining',
                'consent'
            ]);
        });
    }
};
