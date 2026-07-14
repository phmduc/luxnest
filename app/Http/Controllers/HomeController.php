<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Models\Room;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Ưu đãi hôm nay: Hotel rooms rẻ nhất
        $featuredRooms = Room::where('status', 'active')
            ->where('branch', 'Hotel')
            ->orderBy('price')
            ->limit(4)
            ->get();

        // Đang được quan tâm: Villa + Residence
        $trendingRooms = Room::where('status', 'active')
            ->whereIn('branch', ['Villa', 'Residence'])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // Brand showcase: 1 phòng đại diện mỗi chi nhánh
        $branchRooms = Room::where('status', 'active')
            ->get()
            ->groupBy('branch')
            ->map(fn($rooms) => $rooms->first());

        $galleryPhotos = GalleryPhoto::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('home', compact('featuredRooms', 'trendingRooms', 'branchRooms', 'galleryPhotos'));
    }
}
