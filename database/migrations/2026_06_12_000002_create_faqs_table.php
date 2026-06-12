<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->text('question');
            $table->text('answer');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        $rows = [
            [
                'group_name' => 'Đặt phòng & Thanh toán',
                'question'   => 'Tôi có thể đặt phòng trên LuxNest như thế nào?',
                'answer'     => 'Bạn chọn chỗ ở phù hợp, chọn ngày nhận/trả phòng, số khách rồi bấm "Đặt phòng". Hệ thống sẽ kiểm tra phòng trống theo thời gian thực và bạn chỉ cần điền thông tin liên hệ để hoàn tất.',
            ],
            [
                'group_name' => 'Đặt phòng & Thanh toán',
                'question'   => 'LuxNest hỗ trợ những hình thức thanh toán nào?',
                'answer'     => 'Chúng tôi hỗ trợ chuyển khoản ngân hàng, ví điện tử và thanh toán trực tiếp tại cơ sở khi nhận phòng, tùy theo từng chính sách đặt phòng.',
            ],
            [
                'group_name' => 'Đặt phòng & Thanh toán',
                'question'   => 'Tôi có thể hủy hoặc đổi ngày đặt phòng không?',
                'answer'     => 'Có. Vui lòng liên hệ hotline hoặc vào "Bảng điều khiển" cá nhân để kiểm tra điều kiện hủy/đổi của từng đơn đặt phòng cụ thể.',
            ],
            [
                'group_name' => 'Nhận / trả phòng',
                'question'   => 'Giờ nhận phòng và trả phòng là khi nào?',
                'answer'     => 'Giờ nhận phòng tiêu chuẩn là 14:00 và trả phòng trước 12:00 trưa. Nếu cần nhận/trả phòng sớm hoặc muộn, vui lòng báo trước với chúng tôi để được hỗ trợ tùy tình trạng phòng.',
            ],
            [
                'group_name' => 'Nhận / trả phòng',
                'question'   => 'Tôi cần mang theo gì khi nhận phòng?',
                'answer'     => 'Vui lòng mang theo CCCD/CMND hoặc hộ chiếu (đối với khách nước ngoài) trùng với thông tin đã đặt phòng để làm thủ tục check-in.',
            ],
            [
                'group_name' => 'Dịch vụ khác',
                'question'   => 'LuxNest có dịch vụ thuê xe và tour không?',
                'answer'     => 'Có. Bạn có thể tham khảo và đặt dịch vụ thuê xe, tour tại mục "Thuê xe - Tour" trên thanh menu.',
            ],
            [
                'group_name' => 'Dịch vụ khác',
                'question'   => 'Tôi muốn hợp tác/kinh doanh cùng LuxNest, liên hệ ở đâu?',
                'answer'     => 'Vui lòng xem chi tiết tại trang "Hợp tác" hoặc gửi thông tin qua trang "Liên hệ", đội ngũ LuxNest sẽ phản hồi sớm nhất.',
            ],
        ];

        foreach ($rows as $i => $row) {
            $row['sort_order'] = $i + 1;
            $row['created_at'] = now();
            $row['updated_at'] = now();
            DB::table('faqs')->insert($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
