<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('remarketing_subject')->nullable()->after('og_image');
            $table->text('remarketing_greeting')->nullable()->after('remarketing_subject');
            $table->text('remarketing_body')->nullable()->after('remarketing_greeting');
            $table->unsignedTinyInteger('remarketing_discount')->default(10)->after('remarketing_body');
            $table->boolean('remarketing_auto')->default(false)->after('remarketing_discount');
            $table->timestamp('remarketing_send_at')->nullable()->after('remarketing_auto');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'remarketing_subject', 'remarketing_greeting', 'remarketing_body',
                'remarketing_discount', 'remarketing_auto', 'remarketing_send_at',
            ]);
        });
    }
};
