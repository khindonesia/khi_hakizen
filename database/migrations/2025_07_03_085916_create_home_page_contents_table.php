<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('home_page_contents', function (Blueprint $table) {
            $table->id();

            // Hero Section
            $table->string('hero_title');
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_button_text', 100)->nullable();

            // Organization Info
            $table->string('org_name');
            $table->string('org_acronym', 20)->nullable();
            $table->text('org_description')->nullable();

            // Leader Profile
            $table->string('leader_name')->nullable();
            $table->string('leader_position')->nullable();
            $table->string('leader_image')->nullable();
            $table->text('leader_bio')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('home_page_contents');
    }
};
