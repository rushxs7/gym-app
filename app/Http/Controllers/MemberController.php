<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = Member::when(request('searchquery'), function ($query) {
                $query->where('name', 'like', '%' . request('searchquery') . '%')
                    ->orWhere('phone', 'like', '%' . request('searchquery') . '%')
                    ->orWhere('address', 'like', '%' . request('searchquery') . '%')
                    ->orWhere('email', 'like', '%' . request('searchquery') . '%');
            })
            ->when(request('status'), function ($query) {
                switch (request('status')) {
                    case 'active':
                        $query->where('active', 1);
                        break;
                    case 'inactive':
                        $query->where('active', 0);
                        break;
                }
            })
            ->when(request('expiry'), function ($query) {
                $today = Carbon::today()->toDateString();
                $fiveDaysFromToday = Carbon::today()->addDays(5)->toDateString();
                switch (request('expiry')) {
                    case 'valid':
                        $query->whereDate('end_of_membership', '>', $fiveDaysFromToday);
                        break;
                    case 'soon':
                        $query->whereDate('end_of_membership', '<=', $fiveDaysFromToday)
                            ->whereDate('end_of_membership', '>=', $today);
                        break;
                    case 'expired':
                        $query->whereDate('end_of_membership', '<', $today);
                        break;
                }
            })
            ->orderBy('name', 'asc')
            ->paginate(15);

            // dd($members);

        return view('members', ['members' => $members]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
            'address' => 'nullable',
            'gender' => 'nullable',
            'expiry' => 'required|date|after:today'
        ]);


    }
}
