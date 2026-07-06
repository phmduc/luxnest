<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\News;
use App\Models\PageContent;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function about()
    {
        $content = PageContent::dataFor('about');

        return view('pages.about', compact('content'));
    }

    public function faq()
    {
        $faqs = Faq::orderBy('sort_order')->orderBy('id')->get()
            ->groupBy('group_name')
            ->map(fn ($items, $group) => [
                'group' => $group,
                'items' => $items->map(fn ($f) => ['q' => $f->question, 'a' => $f->answer])->values()->all(),
            ])
            ->values()
            ->all();

        return view('pages.faq', compact('faqs'));
    }

    public function partner()
    {
        $content = PageContent::dataFor('partner');

        return view('pages.partner', compact('content'));
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function news()
    {
        $articles = News::where('status', 'active')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('pages.news', compact('articles'));
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'contact_name'    => 'required|string|max:100',
            'contact_email'   => 'required|email|max:255',
            'contact_phone'   => 'required|string|max:20',
            'contact_message' => 'required|string|max:2000',
        ]);

        $adminEmail = Setting::current()->email ?: config('mail.from.address');
        $html = $this->buildEmailHtml('📩 Liên hệ mới từ website', [
            'Họ tên'    => $data['contact_name'],
            'Email'     => $data['contact_email'],
            'Điện thoại'=> $data['contact_phone'],
            'Nội dung'  => nl2br(e($data['contact_message'])),
        ]);

        try {
            Mail::html($html, fn ($m) => $m->to($adminEmail)->subject('📩 Liên hệ mới: ' . $data['contact_name']));
        } catch (\Exception $e) {
            \Log::error('[Contact] Mail error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gửi thất bại, vui lòng thử lại.'], 500);
        }

        return response()->json(['success' => true]);
    }

    public function partnerSubmit(Request $request)
    {
        $data = $request->validate([
            'partner_name'  => 'required|string|max:200',
            'partner_type'  => 'nullable|string|max:100',
            'partner_phone' => 'required|string|max:20',
            'partner_email' => 'required|email|max:255',
            'partner_note'  => 'nullable|string|max:2000',
        ]);

        $adminEmail = Setting::current()->email ?: config('mail.from.address');
        $html = $this->buildEmailHtml('🤝 Đăng ký hợp tác mới', [
            'Đơn vị / cá nhân' => $data['partner_name'],
            'Loại hợp tác'     => $data['partner_type'] ?: '—',
            'Điện thoại'       => $data['partner_phone'],
            'Email'            => $data['partner_email'],
            'Thông tin thêm'   => nl2br(e($data['partner_note'] ?? '')),
        ]);

        try {
            Mail::html($html, fn ($m) => $m->to($adminEmail)->subject('🤝 Đăng ký hợp tác: ' . $data['partner_name']));
        } catch (\Exception $e) {
            \Log::error('[Partner] Mail error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gửi thất bại, vui lòng thử lại.'], 500);
        }

        return response()->json(['success' => true]);
    }

    public function carRentalSubmit(Request $request)
    {
        $data = $request->validate([
            'cr_name'     => 'required|string|max:100',
            'cr_email'    => 'required|email|max:255',
            'cr_phone'    => 'required|string|max:20',
            'cr_car_type' => 'nullable|string|max:100',
            'cr_pickup'   => 'nullable|string|max:50',
            'cr_return'   => 'nullable|string|max:50',
            'cr_note'     => 'nullable|string|max:1000',
        ]);

        $adminEmail = Setting::current()->email ?: config('mail.from.address');
        $html = $this->buildEmailHtml('🚗 Yêu cầu thuê xe mới', [
            'Họ tên'      => $data['cr_name'],
            'Email'       => $data['cr_email'],
            'Điện thoại'  => $data['cr_phone'],
            'Loại xe'     => $data['cr_car_type'] ?: '—',
            'Ngày nhận xe'=> $data['cr_pickup'] ?: '—',
            'Ngày trả xe' => $data['cr_return'] ?: '—',
            'Ghi chú'     => nl2br(e($data['cr_note'] ?? '')),
        ]);

        try {
            Mail::html($html, fn ($m) => $m->to($adminEmail)->subject('🚗 Thuê xe: ' . $data['cr_name'] . ' — ' . ($data['cr_car_type'] ?: 'Chưa chọn xe')));
        } catch (\Exception $e) {
            \Log::error('[CarRental] Mail error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gửi thất bại, vui lòng thử lại.'], 500);
        }

        return response()->json(['success' => true]);
    }

    private function buildEmailHtml(string $title, array $fields): string
    {
        $rows = '';
        foreach ($fields as $label => $value) {
            $rows .= "<tr><td style='padding:8px 12px;background:#f8fafc;font-weight:600;width:160px;color:#475569;border:1px solid #e2e8f0;'>{$label}</td>"
                   . "<td style='padding:8px 12px;border:1px solid #e2e8f0;color:#0f172a;'>{$value}</td></tr>";
        }
        $site = Setting::current()->site_name;
        $time = now()->format('d/m/Y H:i');
        return <<<HTML
        <div style="font-family:Inter,Arial,sans-serif;max-width:600px;margin:0 auto;">
            <div style="background:#1a1a1a;padding:24px 28px;border-radius:12px 12px 0 0;">
                <h2 style="color:#fff;margin:0;font-size:1.2rem;">{$title}</h2>
                <p style="color:#94a3b8;margin:4px 0 0;font-size:0.85rem;">{$site} · {$time}</p>
            </div>
            <div style="border:1px solid #e2e8f0;border-top:none;border-radius:0 0 12px 12px;overflow:hidden;">
                <table style="width:100%;border-collapse:collapse;">{$rows}</table>
            </div>
        </div>
        HTML;
    }

    public function newsShow(string $slug)
    {
        $article = News::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $related = News::where('status', 'active')
            ->where('id', '!=', $article->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('pages.news-show', compact('article', 'related'));
    }
}
