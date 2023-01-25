<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\Visit;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    use HttpResponses;

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

    public function visit(Member $memberId, Request $request)
    {
        $endOfMembership = Carbon::parse($memberId->end_of_membership);
        $today = Carbon::today();

        if (!$memberId->active) {
            return $this->error(['member' => $memberId, 'message' => 'Inactieve member. Prolongeer lidmaatschap eerst.'], 'inactief', 406);
        }
        if ($today->greaterThan($endOfMembership)) {
            return $this->error(['member' => $memberId, 'message' => 'Lidmaatschap vervallen op ' . $endOfMembership->copy()->locale('nl')->toFormattedDateString() . '.'], 'vervallen', 406);
        }

        $todayString = Carbon::today()->toDateString();
        $unclosedVisitsToday = Visit::where('member_id', $memberId->id)
            ->whereDate('time_of_arrival', $todayString)
            ->where('time_of_departure', null)
            ->get();

        // Mark unclosed visits for the day as closed
        foreach ($unclosedVisitsToday as $unclosedVisit) {
            $unclosedVisit->time_of_departure = Carbon::now();
            $unclosedVisit->save();
        }

        $diffInDays = $today->diffInDays($endOfMembership);
        if ($diffInDays == 0) {
            $expiryMessage = 'Vervalt vandaag.';
        } else if ($diffInDays == 1) {
            $expiryMessage = 'Vervalt over 1 dag.';
        } else {
            $expiryMessage = 'Vervalt over ' . $diffInDays . ' dagen.';
        }

        if (!count($unclosedVisitsToday)) {
            $newVisit = Visit::create([
                'member_id' => $memberId->id,
                'time_of_arrival' => Carbon::now(),
            ]);
            return $this->success([
                'visit' => $newVisit,
                'member' => $memberId,
                'message' => $memberId->name . ' aangemeld. Welkom.',
                'expiryMessage' => $expiryMessage
            ], 'aangemeld');
        } else {
            return $this->success([
                'visit' => $unclosedVisitsToday,
                'member' => $memberId,
                'message' => $memberId->name . ' afgemeld. Tot ziens.',
                'expiryMessage' => $expiryMessage
            ], 'afgemeld');
        }
    }

    public function prolong(Member $memberId, Request $request)
    {
        if (!$memberId->active) {
            $memberId->active = 1;
        }
        $diffInDays = Carbon::today()->diffInDays(Carbon::parse($memberId->end_of_membership), false);
        if ($diffInDays < 0) {
            $newExpiryDate = Carbon::today()->addMonthsWithoutOverflow(1);
        } else {
            $newExpiryDate = Carbon::parse($memberId->end_of_membership)->addMonthsWithoutOverflow(1);
        }
        $memberId->end_of_membership = $newExpiryDate;
        $memberId->save();


        return $this->success([
            'verschil' => $diffInDays,
            'member' => $memberId,
            'message' => 'Lidmaatschap geprolongeerd tot en met ' . $newExpiryDate->copy()->locale('nl')->isoFormat('LLLL'),
        ], 'geprolongeerd');
    }
}
