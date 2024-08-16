<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('keyword_index', function (Blueprint $table) {
            $table->id();
            $table->longText('keyword');
            $table->string('language')->nullable();
            $table->text('country')->nullable();
            $table->integer('search_volume')->nullable();
            $table->integer('difficulty')->nullable();
            $table->smallInteger('calculated_difficulty')->nullable();
            $table->text('search_intent')->nullable();
            $table->boolean('location_in_keyword')->nullable();
            $table->boolean('location_reviewed')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_index');
    }
};
