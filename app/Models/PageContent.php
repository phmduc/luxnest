<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $fillable = ['slug', 'data'];

    protected $casts = [
        'data' => 'array',
    ];

    public static function defaults(string $slug): array
    {
        return match ($slug) {
            'about' => [
                'hero_title'    => 'Về LuxNest',
                'hero_subtitle' => 'Không gian nghỉ dưỡng đậm chất Đà Lạt – nơi sự tinh tế trong thiết kế gặp gỡ sự ấm áp trong dịch vụ.',

                'story_title'      => 'Câu chuyện của chúng tôi',
                'story_paragraph_1' => 'LuxNest được xây dựng với mong muốn mang đến cho du khách những kỳ nghỉ trọn vẹn tại Đà Lạt – từ phòng khách sạn tiện nghi, villa riêng tư cho nhóm bạn và gia đình, đến trải nghiệm tham quan thành phố ngàn hoa qua dịch vụ thuê xe và tour trọn gói.',
                'story_paragraph_2' => 'Mỗi không gian tại LuxNest đều được chăm chút về thiết kế và tiện nghi, kết hợp giữa nét hiện đại và hơi thở thiên nhiên đặc trưng của Đà Lạt, giúp khách hàng vừa được nghỉ ngơi thoải mái, vừa cảm nhận trọn vẹn không khí se lạnh, lãng mạn của thành phố sương mù.',

                'why_title' => 'Vì sao chọn LuxNest',

                'why_card_1_icon'  => '🏨',
                'why_card_1_title' => 'Đa dạng chỗ ở',
                'why_card_1_text'  => 'Từ phòng khách sạn, villa đến căn hộ – đáp ứng mọi nhu cầu của khách du lịch, gia đình và nhóm bạn.',

                'why_card_2_icon'  => '🤝',
                'why_card_2_title' => 'Dịch vụ tận tâm',
                'why_card_2_text'  => 'Đội ngũ hỗ trợ 24/7, sẵn sàng tư vấn và đồng hành cùng bạn trong suốt chuyến đi.',

                'why_card_3_icon'  => '🚗',
                'why_card_3_title' => 'Trải nghiệm trọn gói',
                'why_card_3_text'  => 'Kết hợp lưu trú với dịch vụ thuê xe, tour tham quan để hành trình của bạn thêm thuận tiện.',

                'stat_1_number' => '19+',
                'stat_1_label'  => 'Chỗ ở đa dạng',
                'stat_2_number' => '1000+',
                'stat_2_label'  => 'Khách hàng đã phục vụ',
                'stat_3_number' => '4.8/5',
                'stat_3_label'  => 'Đánh giá trung bình',
                'stat_4_number' => '24/7',
                'stat_4_label'  => 'Hỗ trợ khách hàng',

                'cta_title'  => 'Sẵn sàng cho chuyến đi tiếp theo?',
                'cta_text'   => 'Khám phá các chỗ ở của LuxNest và đặt phòng ngay hôm nay.',
                'cta_button' => 'Xem các chỗ ở',
            ],

            'partner' => [
                'hero_title'    => 'Hợp tác cùng LuxNest',
                'hero_subtitle' => 'Bạn đang sở hữu khách sạn, villa, dịch vụ thuê xe hoặc tour du lịch tại Đà Lạt? Cùng đồng hành với LuxNest để tiếp cận thêm nhiều khách hàng.',

                'types_title' => 'Hình thức hợp tác',

                'type_1_icon'  => '🏠',
                'type_1_title' => 'Chủ chỗ ở',
                'type_1_text'  => 'Đưa khách sạn, villa, homestay của bạn lên hệ thống LuxNest để tiếp cận lượng lớn khách du lịch.',

                'type_2_icon'  => '🚐',
                'type_2_title' => 'Đối tác vận chuyển',
                'type_2_text'  => 'Cung cấp dịch vụ thuê xe, đưa đón sân bay cho khách hàng đặt phòng qua LuxNest.',

                'type_3_icon'  => '🗺️',
                'type_3_title' => 'Đối tác tour & trải nghiệm',
                'type_3_text'  => 'Giới thiệu các tour tham quan, hoạt động trải nghiệm tại Đà Lạt đến khách hàng của LuxNest.',

                'benefits_title' => 'Quyền lợi khi hợp tác',

                'benefit_1_icon'  => '📈',
                'benefit_1_title' => 'Tăng lượng khách',
                'benefit_1_text'  => 'Tiếp cận tệp khách hàng đang tìm chỗ ở, thuê xe và tour tại Đà Lạt thông qua LuxNest.',

                'benefit_2_icon'  => '⚙️',
                'benefit_2_title' => 'Vận hành đơn giản',
                'benefit_2_text'  => 'Quản lý đặt phòng, đơn hàng tập trung, không cần đầu tư hệ thống riêng.',

                'benefit_3_icon'  => '🤝',
                'benefit_3_title' => 'Hỗ trợ tận tâm',
                'benefit_3_text'  => 'Đội ngũ LuxNest đồng hành, hỗ trợ đối tác trong suốt quá trình hợp tác.',

                'benefit_4_icon'  => '💰',
                'benefit_4_title' => 'Chính sách minh bạch',
                'benefit_4_text'  => 'Tỷ lệ hoa hồng và chính sách thanh toán rõ ràng, công bằng cho đối tác.',
            ],

            default => [],
        };
    }

    public static function dataFor(string $slug): array
    {
        $row = static::where('slug', $slug)->first();

        return array_merge(static::defaults($slug), $row?->data ?? []);
    }
}
