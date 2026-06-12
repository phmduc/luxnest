<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function faq()
    {
        $faqs = [
            [
                'group' => 'Đặt phòng & Thanh toán',
                'items' => [
                    [
                        'q' => 'Tôi có thể đặt phòng trên LuxNest như thế nào?',
                        'a' => 'Bạn chọn chỗ ở phù hợp, chọn ngày nhận/trả phòng, số khách rồi bấm "Đặt phòng". Hệ thống sẽ kiểm tra phòng trống theo thời gian thực và bạn chỉ cần điền thông tin liên hệ để hoàn tất.',
                    ],
                    [
                        'q' => 'LuxNest hỗ trợ những hình thức thanh toán nào?',
                        'a' => 'Chúng tôi hỗ trợ chuyển khoản ngân hàng, ví điện tử và thanh toán trực tiếp tại cơ sở khi nhận phòng, tùy theo từng chính sách đặt phòng.',
                    ],
                    [
                        'q' => 'Tôi có thể hủy hoặc đổi ngày đặt phòng không?',
                        'a' => 'Có. Vui lòng liên hệ hotline hoặc vào "Bảng điều khiển" cá nhân để kiểm tra điều kiện hủy/đổi của từng đơn đặt phòng cụ thể.',
                    ],
                ],
            ],
            [
                'group' => 'Nhận / trả phòng',
                'items' => [
                    [
                        'q' => 'Giờ nhận phòng và trả phòng là khi nào?',
                        'a' => 'Giờ nhận phòng tiêu chuẩn là 14:00 và trả phòng trước 12:00 trưa. Nếu cần nhận/trả phòng sớm hoặc muộn, vui lòng báo trước với chúng tôi để được hỗ trợ tùy tình trạng phòng.',
                    ],
                    [
                        'q' => 'Tôi cần mang theo gì khi nhận phòng?',
                        'a' => 'Vui lòng mang theo CCCD/CMND hoặc hộ chiếu (đối với khách nước ngoài) trùng với thông tin đã đặt phòng để làm thủ tục check-in.',
                    ],
                ],
            ],
            [
                'group' => 'Dịch vụ khác',
                'items' => [
                    [
                        'q' => 'LuxNest có dịch vụ thuê xe và tour không?',
                        'a' => 'Có. Bạn có thể tham khảo và đặt dịch vụ thuê xe, tour tại mục "Thuê xe - Tour" trên thanh menu.',
                    ],
                    [
                        'q' => 'Tôi muốn hợp tác/kinh doanh cùng LuxNest, liên hệ ở đâu?',
                        'a' => 'Vui lòng xem chi tiết tại trang "Hợp tác" hoặc gửi thông tin qua trang "Liên hệ", đội ngũ LuxNest sẽ phản hồi sớm nhất.',
                    ],
                ],
            ],
        ];

        return view('pages.faq', compact('faqs'));
    }

    public function partner()
    {
        return view('pages.partner');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function news()
    {
        $articles = [
            [
                'title'   => 'LuxNest ra mắt hệ thống đặt phòng trực tuyến mới',
                'excerpt' => 'Trải nghiệm đặt phòng nhanh chóng, kiểm tra phòng trống theo thời gian thực và quản lý đơn đặt phòng ngay trên website LuxNest.',
                'date'    => '10/06/2026',
                'tag'     => 'Thông báo',
            ],
            [
                'title'   => 'Gợi ý lịch trình khám phá Đà Lạt 3 ngày 2 đêm',
                'excerpt' => 'Từ những đồi chè xanh mướt đến các quán cà phê view thung lũng, cùng LuxNest lên kế hoạch cho chuyến đi Đà Lạt đáng nhớ.',
                'date'    => '02/06/2026',
                'tag'     => 'Cẩm nang du lịch',
            ],
            [
                'title'   => 'Ưu đãi đặt phòng sớm dành cho khách hàng thân thiết',
                'excerpt' => 'Đặt phòng trước 30 ngày để nhận nhiều ưu đãi hấp dẫn cho cả phòng khách sạn và villa tại LuxNest.',
                'date'    => '20/05/2026',
                'tag'     => 'Ưu đãi',
            ],
        ];

        return view('pages.news', compact('articles'));
    }
}
