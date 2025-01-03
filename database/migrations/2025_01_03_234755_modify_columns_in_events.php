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
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->change();
            $table->unsignedInteger('expected_participants')->nullable()->change();
            $table->unsignedTinyInteger('type')->nullable()->after('expected_participants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable(false)->change();
            $table->unsignedInteger('expected_participants')->nullable(false)->change();
            $table->dropColumn('type');
        });
    }
};
