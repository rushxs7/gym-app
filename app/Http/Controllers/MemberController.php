<?php

namespace App\Http\Controllers;

use App\Http\Resources\MemberResource;
use App\Http\Resources\MemberResourceCollection;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Visit;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Settings\AutomationSettings;
use App\Settings\PaymentSettings;
use Illuminate\Validation\Rule;

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
            // ->when(request('status'), function ($query) {
            //     switch (request('status')) {
            //         case 'active':
            //             $query->where('active', 1);
            //             break;
            //         case 'inactive':
            //             $query->where('active', 0);
            //             break;
            //     }
            // })
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
            ->with(['visits' => function ($query) {
                $query->whereNotNull('time_of_arrival')
                    ->whereNull('time_of_departure')
                    ->get();
            }])
            ->orderBy('name', 'asc')
            ->paginate(15);

            // return new MemberResourceCollection($members);

        return view('members.index', ['members' => $members]);
    }

    public function show(Member $member)
    {
        $member->load(['visits', 'payments']);
        return new MemberResource($member);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'birthday' => [
                'nullable',
                'date',
            ],
            'email' => 'nullable|email',
            'address' => 'nullable',
            'gender' => 'nullable',
            'expiry' => 'required|date|after:today',
            'balance' => 'required_if:registration,true',
        ]);

        $member = new Member();
        $member->name = $request->name;
        $member->phone = $request->phone;
        $member->birthday = Carbon::parse($request->birthday);
        $member->email = $request->email;
        $member->address = $request->address;
        $member->gender = $request->gender;
        $member->end_of_membership = Carbon::parse($request->expiry);
        $member->save();

        if (request('registration')) {
            Payment::create([
                'member_id' => $member->id,
                'balance' => (float) $request->balance,
                'type' => 'INS',
                'description' => 'Inschrijving Lid #' . $member->id . ' (' . $member->name . ')',
            ]);
        }

        return redirect()->route('members.index')->with('success', 'Member succesvol opgeslagen.');
    }

    public function edit(Member $member, Request $request)
    {
        $member->load(['visits', 'payments']);
        return view('members.edit', ['member' => $member]);
    }

    public function visit(Member $member, Request $request)
    {
        $endOfMembership = Carbon::parse($member->end_of_membership);
        $today = Carbon::today();

        // if (!$member->active) {
        //     return $this->error(['member' => $member, 'message' => 'Inactieve member. Prolongeer lidmaatschap eerst.'], 'inactief', 406);
        // }
        if ($today->greaterThan($endOfMembership)) {
            return $this->error(['member' => $member, 'message' => 'Lidmaatschap vervallen op ' . $endOfMembership->copy()->locale('nl')->toFormattedDateString() . '.'], 'vervallen', 406);
        }

        $todayString = Carbon::today()->toDateString();
        $unclosedVisitsToday = Visit::where('member_id', $member->id)
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
            $expiryMessage = 'Lidmaatschap vervalt vandaag.';
        } else if ($diffInDays == 1) {
            $expiryMessage = 'Lidmaatschap vervalt over 1 dag.';
        } else {
            $expiryMessage = 'Lidmaatschap vervalt over ' . $diffInDays . ' dagen.';
        }

        if (!count($unclosedVisitsToday)) {
            $newVisit = Visit::create([
                'member_id' => $member->id,
                'time_of_arrival' => Carbon::now(),
            ]);
            return $this->success([
                'visit' => $newVisit,
                'member' => $member,
                'message' => $member->name . ' aangemeld. Welkom.',
                'expiryMessage' => $expiryMessage
            ], 'aangemeld');
        } else {
            return $this->success([
                'visit' => $unclosedVisitsToday,
                'member' => $member,
                'message' => $member->name . ' afgemeld. Tot ziens.',
                'expiryMessage' => $expiryMessage
            ], 'afgemeld');
        }
    }

    public function promptprolongation(Member $member, Request $request)
    {
        $diffInDays = Carbon::today()->diffInDays(Carbon::parse($member->end_of_membership), false);
        if ($diffInDays < 0) {
            $newExpiryDate = Carbon::today()
                ->addMonthsWithoutOverflow(1)
                ->locale('nl')
                ->isoFormat('LL');
        } else {
            $newExpiryDate = Carbon::parse($member->end_of_membership)
                ->addMonthsWithoutOverflow(1)
                ->locale('nl')
                ->isoFormat('LL');
        }

        $penaltyFee = (float) 0;
        $dateDifference = Carbon::parse($member->end_of_membership)
            ->diffInDays(Carbon::today());

        if ($dateDifference > app(AutomationSettings::class)->days_after_expiration_before_fine) {
            $penaltyFee = (float) app(PaymentSettings::class)->penalty_fee;
        }

        return $this->success([
            'member' => $member,
            'price' => (float) app(PaymentSettings::class)->prolongation_fee + $penaltyFee,
            'penalty' => $penaltyFee,
            'proposed_date' => $newExpiryDate
        ], 'proposeddate');
    }

    public function prolong(Member $member, Request $request)
    {
        // if (!$member->active) {
        //     $member->active = 1;
        // }
        $diffInDays = Carbon::today()->diffInDays(Carbon::parse($member->end_of_membership), false);
        if ($diffInDays < 0) {
            $newExpiryDate = Carbon::today()->addMonthsWithoutOverflow(1);
        } else {
            $newExpiryDate = Carbon::parse($member->end_of_membership)->addMonthsWithoutOverflow(1);
        }
        $member->end_of_membership = $newExpiryDate;
        $member->save();

        $prolongationPayment = Payment::create([
            'member_id' => $member->id,
            'balance' => (float) app(PaymentSettings::class)->prolongation_fee,
            'type' => 'PRO',
            'description' => 'Prolongatie lidmaatschap t/m ' . Carbon::parse($member->end_of_membership)->locale('nl')->isoFormat('LL'),
        ]);

        $dateDifference = Carbon::parse($member->end_of_membership)
            ->diffInDays(Carbon::today());
        if ($dateDifference > app(AutomationSettings::class)->days_after_expiration_before_fine) {
            Payment::create([
                'member_id' => $member->id,
                'balance' => (float) app(PaymentSettings::class)->penalty_fee,
                'type' => 'BOT',
                'description' => 'Boete prolongatie betaling #' . $prolongationPayment->id,
            ]);
        }

        return $this->success([
            'verschil' => $diffInDays,
            'member' => $member,
            'message' => 'Lidmaatschap geprolongeerd tot en met ' . $newExpiryDate->copy()->locale('nl')->isoFormat('LL'),
        ], 'geprolongeerd');
    }

    public function apiIndex(Request $request)
    {
        return null;
    }
}
