<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        // Backfill slugs for existing rows
        $rows = DB::table('news')->get();
        foreach ($rows as $row) {
            $base = Str::slug($row->title) ?: 'bai-viet-' . $row->id;
            $slug = $base;
            $i = 2;
            while (DB::table('news')->where('slug', $slug)->where('id', '!=', $row->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            DB::table('news')->where('id', $row->id)->update(['slug' => $slug]);
        }

        Schema::table('news', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
