<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    /** slug => tên hiển thị của các trang sửa được nội dung */
    public const EDITABLE = [
        'about'       => 'Trang Giới thiệu',
        'partner'     => 'Trang Hợp tác',
        'car-rental'  => 'Trang Thuê xe',
    ];

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

            'car-rental' => [
                // Toàn bộ nội dung trang, soạn bằng trình soạn thảo trong admin.
                // Đặt [form] ở chỗ muốn hiện form liên hệ; không có thì form nằm cuối trang.
                'content_html' => '<h1>🚗 Dịch Vụ Cho Thuê Xe</h1><p>Đặt xe kèm lái xe riêng 24/7 – phục vụ tận nơi tại LuxNest.</p><div class="table-scroll"><table><thead><tr><th>Loại xe</th><th>Mẫu xe</th><th>Giá từ (VNĐ/ngày)</th><th>Ghi chú</th></tr></thead><tbody><tr><td>Sedan 4 chỗ</td><td>Toyota Camry / Mazda 6</td><td>800.000đ</td><td>Phù hợp cặp đôi, công tác</td></tr><tr><td>MPV 7 chỗ</td><td>Toyota Innova / Mitsubishi Xpander</td><td>1.200.000đ</td><td>Gia đình, nhóm nhỏ</td></tr><tr><td>MPV Hạng Sang</td><td>Toyota Alphard 2024</td><td>3.500.000đ</td><td>VIP, sự kiện, đón sân bay</td></tr><tr><td>Sedan Hạng Sang</td><td>Mercedes-Benz E/S Class</td><td>4.500.000đ</td><td>Hội nghị, đối tác cao cấp</td></tr><tr><td>Xe 9 chỗ</td><td>Hyundai Starex / Ford Transit 9</td><td>1.600.000đ</td><td>Nhóm gia đình, du lịch</td></tr><tr><td>Xe 16 chỗ</td><td>Ford Transit 16 / Hyundai County</td><td>2.200.000đ</td><td>Đoàn lớn, team building</td></tr></tbody></table></div><p><em>* Giá trên chưa bao gồm phí xăng dầu và đường cao tốc. Liên hệ để nhận báo giá cụ thể.</em></p><h2>📋 Để lại thông tin – Chúng tôi sẽ liên hệ lại trong 15 phút</h2>[form]',
            ],

            default => [],
        };
    }

    /**
     * Ghép dữ liệu gửi lên vào đúng khuôn mặc định của trang: khóa `*_html`
     * đi qua bộ lọc HTML, khóa dạng mảng (vd bảng giá xe) giữ đúng cột.
     */
    public static function normalize(array $defaults, array $input): array
    {
        $data = [];

        foreach ($defaults as $key => $default) {
            if (is_array($default)) {
                $rows    = is_array($input[$key] ?? null) ? $input[$key] : $default;
                $columns = array_keys($default[0] ?? []);

                $data[$key] = collect($rows)
                    ->filter(fn ($row) => is_array($row))
                    ->take(50)
                    ->map(fn ($row) => collect($columns)
                        ->mapWithKeys(fn ($col) => [$col => trim((string) ($row[$col] ?? ''))])
                        ->all())
                    ->filter(fn ($row) => implode('', $row) !== '')
                    ->values()
                    ->all();

                continue;
            }

            $value = (string) ($input[$key] ?? $default);

            $data[$key] = str_ends_with($key, '_html') ? HtmlSanitizer::clean($value) : $value;
        }

        return $data;
    }

    public static function dataFor(string $slug): array
    {
        $row = static::where('slug', $slug)->first();

        return array_merge(static::defaults($slug), $row?->data ?? []);
    }
}
