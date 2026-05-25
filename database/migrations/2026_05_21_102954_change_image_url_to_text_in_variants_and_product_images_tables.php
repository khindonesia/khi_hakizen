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
        Schema::table('variants', function (Blueprint $table) {
            $table->text('image_url')->nullable()->change();
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->text('image_url')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->string('image_url')->nullable()->change();
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->string('image_url')->change();
        });
    }
};
