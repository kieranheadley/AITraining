<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->boolean('assignment_flagged')->default(0)->nullable()->after('new_page');
            $table->string('assignment_flag_reason')->nullable()->after('assignment_flagged');
            $table->longText('assignment_flag_notes')->nullable()->after('assignment_flag_reason');
        });
    }

    public function down(): void
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropColumn('assignment_flagged');
            $table->dropColumn('assignment_flag_reason');
            $table->dropColumn('assignment_flag_notes');
        });
    }
};
