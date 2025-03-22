<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationsTable extends Migration
{
    /**
     * Run the migrations to create the organizations table.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id(); // Primary key column
            $table->string('name'); // Column for the organization's name
            $table->string('avatar')->nullable(); // Column for avatar (nullable if not available)
            $table->string('position'); // Column for the position or role within the organization
            $table->text('description'); // Column for the organization's description
            $table->string('facebook_url')->nullable(); // Column for the Facebook URL (nullable)
            $table->string('instagram_url')->nullable(); // Column for the Instagram URL (nullable)
            $table->string('linkedin_url')->nullable(); // Column for the LinkedIn URL (nullable)
            $table->string('twitter_url')->nullable(); // Column for the Twitter URL (nullable)
            $table->timestamps(); // Created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations by dropping the organizations table.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organizations');
    }
}
