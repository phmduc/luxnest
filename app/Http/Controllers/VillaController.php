<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\VillaListing;
use Illuminate\Http\Request;

class VillaController extends Controller
{
    public function index(Request $request)
    {
        $location = $request->input('location', 'Đà Lạt');

        $branches = VillaListing::where('status', 'active')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->all();

        if (empty($branches)) {
            $branches = ['Đà Lạt'];
        }

        $matched  = collect($branches)->first(fn ($b) => mb_strtolower($b) === mb_strtolower($location));
        $location = $matched ?? $branches[0];

        $villas = VillaListing::where('status', 'active')
            ->where('location', $location)
            ->orderBy('name')
            ->get();

        $settings = Setting::current();

        return view('villa.index', compact('villas', 'branches', 'location', 'settings'));
    }

    public function show(string $slug)
    {
        $villa = VillaListing::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $gallery = collect([$villa->image])
            ->merge($villa->gallery ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();

        $others = VillaListing::where('status', 'active')
            ->where('location', $villa->location)
            ->where('id', '!=', $villa->id)
            ->limit(3)
            ->get();

        $settings = Setting::current();

        return view('villa.show', compact('villa', 'gallery', 'others', 'settings'));
    }
}
