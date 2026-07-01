<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\News;
use App\Models\PageContent;

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
