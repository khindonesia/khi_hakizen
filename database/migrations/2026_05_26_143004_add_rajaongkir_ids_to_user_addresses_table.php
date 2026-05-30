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
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('province_id')->nullable()->after('state');
            $table->string('city_id')->nullable()->after('city');
            $table->string('district_id')->nullable()->after('district');
            $table->string('subdistrict_id')->nullable()->after('village');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn(['province_id', 'city_id', 'district_id', 'subdistrict_id']);
        });
    }
};
