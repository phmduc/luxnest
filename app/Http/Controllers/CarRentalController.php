<?php

namespace App\Http\Controllers;

use App\Models\PageContent;

class CarRentalController extends Controller
{
    public function index()
    {
        return view('car-rental.index', ['page' => PageContent::dataFor('car-rental')]);
    }
}
