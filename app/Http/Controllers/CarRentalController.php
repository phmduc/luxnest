<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CarRentalController extends Controller
{
    public function index()
    {
        $cars = [
            ['type' => 'Sedan 4 chỗ',    'model' => 'Toyota Camry / Mazda 6',            'price' => '800.000',   'note' => 'Phù hợp cặp đôi, công tác'],
            ['type' => 'MPV 7 chỗ',       'model' => 'Toyota Innova / Mitsubishi Xpander','price' => '1.200.000', 'note' => 'Gia đình, nhóm nhỏ'],
            ['type' => 'MPV Hạng Sang',   'model' => 'Toyota Alphard 2024',               'price' => '3.500.000', 'note' => 'VIP, sự kiện, đón sân bay'],
            ['type' => 'Sedan Hạng Sang', 'model' => 'Mercedes-Benz E/S Class',           'price' => '4.500.000', 'note' => 'Hội nghị, đối tác cao cấp'],
            ['type' => 'Xe 9 chỗ',        'model' => 'Hyundai Starex / Ford Transit 9',   'price' => '1.600.000', 'note' => 'Nhóm gia đình, du lịch'],
            ['type' => 'Xe 16 chỗ',       'model' => 'Ford Transit 16 / Hyundai County',  'price' => '2.200.000', 'note' => 'Đoàn lớn, team building'],
        ];

        return view('car-rental.index', compact('cars'));
    }
}
