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
            // null = one-shot, 'daily' | 'weekly'
            $table->string('repeat_interval', 20)->nullable()->after('send_at');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropColumn('repeat_interval');
        });
    }
};
