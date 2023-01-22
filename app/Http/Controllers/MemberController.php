<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = Member::when(request('searchquery'), function ($query) {
                $query->where('id', request('searchquery'))
                    ->orWhere('name', 'like', '%' . request('searchquery') . '%')
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
            'expiry' => 'required|date|after:today',
            'paid' => 'required_if:registration,true',
            'retour' => 'required_if:registration,true'
        ]);

        $member = new Member();
        $member->name = $request->name;
        $member->phone = $request->phone;
        $member->email = $request->email;
        $member->address = $request->address;
        $member->gender = $request->gender;
        $member->end_of_membership = Carbon::parse($request->expiry);

        $member->active = true;
        $member->rfid_code = Str::uuid();
        $member->save();

        if (request('registration')) {
            Payment::create([
                'member_id' => $member->id,
                'received_amount' => (float) $request->paid,
                'returned_amount' => (float) $request->retour,
                'balance' => (float) ($request->paid - $request->retour),
                'type' => 'INS',
                'description' => 'Inschrijving Lid #' . $member->id . ' (' . $member->name . ')',
            ]);
        }

        return redirect()->route('members.index')->with('success', 'Member succesvol opgeslagen.');
    }
}
