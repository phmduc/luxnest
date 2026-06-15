<?php

namespace Database\Seeders;

use App\Models\VillaListing;
use Illuminate\Database\Seeder;

class VillaListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $villas = [
            [
                'slug' => 'villa-6v4a',
                'name' => 'VILLA 6V4A',
                'location_desc' => 'Triệu Việt Vương, Đà Lạt',
                'beds' => '6 Phòng',
                'guests' => '12 Khách',
                'image' => '/storage/villas/z7793029455611_a358ca8ee526f2b33ecfd50492f5bee9.jpg',
            ],
            [
                'slug' => 'villa-7v85a',
                'name' => 'VILLA 7V85A',
                'location_desc' => 'Lữ Gia, Đà Lạt',
                'beds' => '7 Phòng',
                'guests' => '14 Khách',
                'image' => '/storage/villas/z7793029464315_40e01419e5176684ab7ea2fbe6bf32b6.jpg',
            ],
            [
                'slug' => 'villa-4v7a',
                'name' => 'VILLA 4V7A',
                'location_desc' => 'Dan Kia, Đà Lạt',
                'beds' => '4 Phòng',
                'guests' => '8 Khách',
                'image' => '/storage/villas/z7793029467962_6e1d959b2f630b26c11181f33e7957a1.jpg',
            ],
            [
                'slug' => 'villa-6v12a',
                'name' => 'VILLA 6V12A',
                'location_desc' => 'Huỳnh Tấn Phát, Đà Lạt',
                'beds' => '6 Phòng',
                'guests' => '12 Khách',
                'image' => '/storage/villas/z7793029470257_9106b3cf645ac7bf9145ddc6bcbfcc05.jpg',
            ],
            [
                'slug' => 'villa-5v9a',
                'name' => 'VILLA 5V9A',
                'location_desc' => 'Nguyễn Đình Chiểu, Đà Lạt',
                'beds' => '5 Phòng',
                'guests' => '10 Khách',
                'image' => '/storage/villas/z7793029481666_3595ac900e5c06957d641a77696a8bca.jpg',
            ],
            [
                'slug' => 'villa-4v4a',
                'name' => 'VILLA 4V4A',
                'location_desc' => 'Nguyễn Trung Trực, Đà Lạt',
                'beds' => '4 Phòng',
                'guests' => '8 Khách',
                'image' => '/storage/villas/z7793029479431_5c3b9537776be934ea7927d6fc047400.jpg',
            ],
            [
                'slug' => 'villa-6v8a',
                'name' => 'VILLA 6V8A',
                'location_desc' => 'Khe Sanh, Đà Lạt',
                'beds' => '6 Phòng',
                'guests' => '12 Khách',
                'image' => '/storage/villas/z7793029486724_11512eea50b20654d04a47104b3d88cf.jpg',
            ],
            [
                'slug' => 'villa-3va',
                'name' => 'VILLA 3VA',
                'location_desc' => 'Nguyễn Hữu Cảnh, Đà Lạt',
                'beds' => '3 Phòng',
                'guests' => '6 Khách',
                'image' => '/storage/villas/z7793029493412_9b6562f7e6f0bc52d500e9af8565db54.jpg',
            ],
            [
                'slug' => 'homestay-5h6a',
                'name' => 'HOMESTAY 5H6A',
                'location_desc' => 'Phạm Hồng Thái, Đà Lạt',
                'beds' => '5 Phòng',
                'guests' => '10 Khách',
                'image' => '/storage/villas/z7793029499789_9a990dc88b709e09328ee3e74e979f12.jpg',
            ],
            [
                'slug' => 'homestay-4h4a-1',
                'name' => 'HOMESTAY 4H4A',
                'location_desc' => 'Hoàng Hoa Thám, Đà Lạt',
                'beds' => '2 Phòng',
                'guests' => '4 Khách',
                'image' => '/storage/villas/z7793029513747_00fef0d3b1dabc87753854cf75edbb2a.jpg',
            ],
            [
                'slug' => 'homestay-4h4a-2',
                'name' => 'HOMESTAY 4H4A',
                'location_desc' => 'Hoàng Hoa Thám, Đà Lạt',
                'beds' => '5 Phòng',
                'guests' => '10 Khách',
                'image' => '/storage/villas/z7793029518228_ce3cd53b29ddab1983b8ec1e62c91042.jpg',
            ],
            [
                'slug' => 'homestay-4h4a-3',
                'name' => 'HOMESTAY 4H4A',
                'location_desc' => 'Hoàng Hoa Thám, Đà Lạt',
                'beds' => '2 Phòng',
                'guests' => '4 Khách',
                'image' => '/storage/villas/z7793029526783_3fe470157adf880f95fb80f6f70ff6af.jpg',
            ],
        ];

        foreach ($villas as $villa) {
            VillaListing::updateOrCreate(
                ['slug' => $villa['slug']],
                array_merge($villa, [
                    'location' => 'Đà Lạt',
                    'description' => null,
                    'gallery' => [],
                    'status' => 'active',
                ])
            );
        }
    }
}
