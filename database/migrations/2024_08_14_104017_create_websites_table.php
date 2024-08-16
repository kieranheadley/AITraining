<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('website_url');
            $table->string('crawl_location')->nullable();
            $table->string('serp_id')->nullable();
            $table->tinyInteger('process_stage')->default(0);
            $table->tinyInteger('processing')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
