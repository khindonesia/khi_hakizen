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
        Schema::table('home_achievements', function (Blueprint $table) {
            $table->foreignId('home_page_content_id')->nullable()->after('id')->constrained('home_page_contents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_achievements', function (Blueprint $table) {
            $table->dropForeign(['home_page_content_id']);
            $table->dropColumn('home_page_content_id');
        });
    }
};
