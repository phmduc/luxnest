<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('tag')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->date('published_at')->nullable();
            $table->enum('status', ['active', 'draft'])->default('active');
            $table->timestamps();
        });

        DB::table('news')->insert([
            [
                'title'        => 'LuxNest ra mắt hệ thống đặt phòng trực tuyến mới',
                'excerpt'      => 'Trải nghiệm đặt phòng nhanh chóng, kiểm tra phòng trống theo thời gian thực và quản lý đơn đặt phòng ngay trên website LuxNest.',
                'tag'          => 'Thông báo',
                'published_at' => '2026-06-10',
                'status'       => 'active',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'title'        => 'Gợi ý lịch trình khám phá Đà Lạt 3 ngày 2 đêm',
                'excerpt'      => 'Từ những đồi chè xanh mướt đến các quán cà phê view thung lũng, cùng LuxNest lên kế hoạch cho chuyến đi Đà Lạt đáng nhớ.',
                'tag'          => 'Cẩm nang du lịch',
                'published_at' => '2026-06-02',
                'status'       => 'active',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'title'        => 'Ưu đãi đặt phòng sớm dành cho khách hàng thân thiết',
                'excerpt'      => 'Đặt phòng trước 30 ngày để nhận nhiều ưu đãi hấp dẫn cho cả phòng khách sạn và villa tại LuxNest.',
                'tag'          => 'Ưu đãi',
                'published_at' => '2026-05-20',
                'status'       => 'active',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
