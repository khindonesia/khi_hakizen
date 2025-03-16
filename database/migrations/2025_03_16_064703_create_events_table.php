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
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id'); // Auto-incrementing UNSIGNED INTEGER (primary key)
            $table->unsignedBigInteger('author_id'); // UNSIGNED INTEGER for the foreign key to the users table
            $table->string('title', 191); // VARCHAR equivalent column
            $table->string('seo_title', 191)->nullable(); // VARCHAR equivalent column, nullable
            $table->text('body'); // TEXT column for the event content
            $table->string('image', 191)->nullable(); // VARCHAR equivalent column, nullable for the image path
            $table->string('slug', 191)->unique(); // VARCHAR equivalent column with a unique index for the slug
            $table->text('meta_description')->nullable(); // TEXT column, nullable for the meta description
            $table->text('meta_keywords')->nullable(); // TEXT column, nullable for the meta keywords
            $table->enum('status', ['PUBLISHED', 'DRAFT', 'PENDING'])->default('DRAFT'); // ENUM column for the status with a default value
            $table->dateTime('start_datetime'); // Date and time when the event starts
            $table->dateTime('end_datetime'); // Date and time when the event ends
            $table->timestamps(); // Adds created_at and updated_at columns

            // Foreign key constraints
            $table->foreign('author_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};