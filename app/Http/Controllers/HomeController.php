<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $activeMembers = Member::where('active', 1)->count();
        $newMembersThisMonth = Member::whereMonth('created_at', Carbon::now()->month)->count();
        $visitsYesterday = Visit::whereDate('time_of_arrival', Carbon::yesterday()->toDateString())->count();
        $visitsToday = Visit::whereDate('time_of_arrival', Carbon::today()->toDateString())->count();
        $revenueThisMonth = Payment::whereMonth('created_at', Carbon::today()->month)->sum('balance');

        if ($visitsToday != 0 && $visitsYesterday != 0) {
            $visitorDifference = ((number_format(($visitsToday / $visitsYesterday), 2)) * 100) - 100;
        } else {
            $visitorDifference = 0;
        }

        return view('home', [
            'activeMembers' => $activeMembers,
            'newMembersThisMonth' => $newMembersThisMonth,
            'visitsToday' => $visitsToday,
            'visitorDifference' => $visitorDifference,
            'revenueThisMonth' => $revenueThisMonth,
        ]);
    }
}
