<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('keyword_locations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('location_id');
            $table->bigInteger('location_id_parent');
            $table->string('location_name');
            $table->string('location_type');
            $table->string('location_country_iso_code');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_locations');
    }
};
