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
        Schema::table('email_campaigns', function (Blueprint $table) {
            // eligible | manual | members
            $table->string('recipient_mode', 20)->default('eligible')->after('conditions');
            // email array (manual mode) OR user_id array (members mode)
            $table->json('recipient_data')->nullable()->after('recipient_mode');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropColumn(['recipient_mode', 'recipient_data']);
        });
    }
};
