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
        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('typeables', function (Blueprint $table) {
            $table->foreignId('type_id')->constrained('types')->onDelete('cascade');
            $table->unsignedBigInteger('typeable_id');
            $table->string('typeable_type');

            $table->unique(['type_id', 'typeable_id', 'typeable_type']);
            $table->index(['typeable_id', 'typeable_type']);
        });

        // Seed default types
        \DB::table('types')->insert([
            [
                'name' => 'Best Seller',
                'slug' => 'best-seller',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ads',
                'slug' => 'ads',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Terbaru',
                'slug' => 'terbaru',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Terlama',
                'slug' => 'terlama',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('typeables');
        Schema::dropIfExists('types');
    }
};
