<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id');
            $table->string('keyword');
            $table->string('assigned_page')->nullable();
            $table->json('embedding_results')->nullable();
            $table->longText('selection_reason')->nullable();
            $table->boolean('selected')->nullable();
            $table->boolean('new_page')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keywords');
    }
};
