<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        return view('visits');
    }
}
