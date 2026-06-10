<?php

namespace App\Http\Controllers;

use App\Models\Room;

class HotelController extends Controller
{
    public function show(string $slug)
    {
        $room = Room::where('slug', $slug)
                    ->where('status', 'active')
                    ->firstOrFail();

        // All images: main + gallery
        $images = array_values(array_filter(
            array_merge([$room->image], $room->gallery ?? [])
        ));

        // Amenity → icon mapping
        $iconMap = [
            'wi-fi'     => 'fa-solid fa-wifi',
            'wifi'      => 'fa-solid fa-wifi',
            'điều hòa'  => 'fa-solid fa-snowflake',
            'tv'        => 'fa-solid fa-tv',
            'nước nóng' => 'fa-solid fa-shower',
            'hồ bơi'    => 'fa-solid fa-person-swimming',
            'bể bơi'    => 'fa-solid fa-person-swimming',
            'spa'       => 'fa-solid fa-spa',
            'bữa sáng'  => 'fa-solid fa-mug-saucer',
            'gym'       => 'fa-solid fa-dumbbell',
            'nhà hàng'  => 'fa-solid fa-utensils',
            'đỗ xe'     => 'fa-solid fa-square-parking',
            'bar'       => 'fa-solid fa-martini-glass',
        ];

        $highlights = collect($room->amenities ?? [])->map(function ($am) use ($iconMap) {
            $icon = 'fa-solid fa-check';
            foreach ($iconMap as $keyword => $ic) {
                if (mb_stripos($am, $keyword) !== false) {
                    $icon = $ic;
                    break;
                }
            }
            return ['icon' => $icon, 'text' => $am];
        })->values()->all();

        // Similar rooms in same branch (for the "rooms available" section)
        $similarRooms = Room::where('branch', $room->branch)
            ->where('id', '!=', $room->id)
            ->where('status', 'active')
            ->limit(3)
            ->get();

        $roomsForDisplay = $similarRooms
            ->map(fn($r) => [
                'name'      => $r->name,
                'image'     => $r->image,
                'amenities' => $r->amenities ?? [],
                'price'     => $r->price,
                'old_price' => ($r->regular_price && $r->regular_price > $r->price)
                                ? $r->regular_price : null,
                'slug'      => $r->slug,
            ])->all();

        // Stars by branch
        $stars = match($room->branch) {
            'Villa'     => 5,
            'Residence' => 5,
            default     => 4,
        };

        $hotel = [
            'name'          => $room->name,
            'branch'        => $room->branch,
            'stars'         => $stars,
            'rating'        => 9.1,
            'reviews_count' => 124,
            'rating_text'   => 'Tuyệt vời',
            'address'       => $room->branch . ' — LuxNest',
            'images'        => $images,
            'description'   => $room->description
                               ?? 'Không gian nghỉ dưỡng sang trọng, tiện nghi đầy đủ tại LuxNest '.$room->branch.'.',
            'highlights'    => $highlights,
            'rooms'         => $roomsForDisplay,
        ];

        return view('hotel.show', compact('hotel', 'room'));
    }
}
