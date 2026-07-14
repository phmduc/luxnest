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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // MySQL: expand ENUM in place
            DB::statement("ALTER TABLE email_campaigns MODIFY COLUMN status ENUM('draft','scheduled','sending','sent','recurring') NOT NULL DEFAULT 'draft'");
        } else {
            // SQLite: enum is a CHECK constraint — convert to plain string
            Schema::table('email_campaigns', function (Blueprint $table) {
                $table->string('status', 20)->default('draft')->change();
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE email_campaigns MODIFY COLUMN status ENUM('draft','scheduled','sending','sent') NOT NULL DEFAULT 'draft'");
        }
    }
};
