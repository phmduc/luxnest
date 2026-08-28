<?php

namespace App\Http\Controllers;

use App\Models\PageContent;

class CarRentalController extends Controller
{
    public function index()
    {
        $page = PageContent::dataFor('car-rental');
        $cars = $page['cars'] ?? [];

        return view('car-rental.index', compact('page', 'cars'));
    }
}
