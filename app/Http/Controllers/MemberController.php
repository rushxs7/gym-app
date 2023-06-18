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

        return view('members', ['members' => $members]);
    }

    public function show(Member $memberId)
    {
        $memberId->load(['visits', 'payments']);
        return new MemberResource($memberId);
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

    public function edit(Member $memberId, Request $request)
    {

    }

    public function visit(Member $memberId, Request $request)
    {
        $endOfMembership = Carbon::parse($memberId->end_of_membership);
        $today = Carbon::today();

        // if (!$memberId->active) {
        //     return $this->error(['member' => $memberId, 'message' => 'Inactieve member. Prolongeer lidmaatschap eerst.'], 'inactief', 406);
        // }
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
            $expiryMessage = 'Lidmaatschap vervalt vandaag.';
        } else if ($diffInDays == 1) {
            $expiryMessage = 'Lidmaatschap vervalt over 1 dag.';
        } else {
            $expiryMessage = 'Lidmaatschap vervalt over ' . $diffInDays . ' dagen.';
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

    public function promptprolongation(Member $memberId, Request $request)
    {
        $diffInDays = Carbon::today()->diffInDays(Carbon::parse($memberId->end_of_membership), false);
        if ($diffInDays < 0) {
            $newExpiryDate = Carbon::today()
                ->addMonthsWithoutOverflow(1)
                ->locale('nl')
                ->isoFormat('LL');
        } else {
            $newExpiryDate = Carbon::parse($memberId->end_of_membership)
                ->addMonthsWithoutOverflow(1)
                ->locale('nl')
                ->isoFormat('LL');
        }

        $penaltyFee = (float) 0;
        $dateDifference = Carbon::parse($memberId->end_of_membership)
            ->diffInDays(Carbon::today());

        if ($dateDifference > app(AutomationSettings::class)->days_after_expiration_before_fine) {
            $penaltyFee = (float) app(PaymentSettings::class)->penalty_fee;
        }

        return $this->success([
            'member' => $memberId,
            'price' => (float) app(PaymentSettings::class)->prolongation_fee + $penaltyFee,
            'penalty' => $penaltyFee,
            'proposed_date' => $newExpiryDate
        ], 'proposeddate');
    }

    public function prolong(Member $memberId, Request $request)
    {
        // if (!$memberId->active) {
        //     $memberId->active = 1;
        // }
        $diffInDays = Carbon::today()->diffInDays(Carbon::parse($memberId->end_of_membership), false);
        if ($diffInDays < 0) {
            $newExpiryDate = Carbon::today()->addMonthsWithoutOverflow(1);
        } else {
            $newExpiryDate = Carbon::parse($memberId->end_of_membership)->addMonthsWithoutOverflow(1);
        }
        $memberId->end_of_membership = $newExpiryDate;
        $memberId->save();

        $prolongationPayment = Payment::create([
            'member_id' => $memberId->id,
            'balance' => (float) app(PaymentSettings::class)->prolongation_fee,
            'type' => 'PRO',
            'description' => 'Prolongatie lidmaatschap t/m ' . Carbon::parse($memberId->end_of_membership)->locale('nl')->isoFormat('LL'),
        ]);

        $dateDifference = Carbon::parse($memberId->end_of_membership)
            ->diffInDays(Carbon::today());
        if ($dateDifference > app(AutomationSettings::class)->days_after_expiration_before_fine) {
            Payment::create([
                'member_id' => $memberId->id,
                'balance' => (float) app(PaymentSettings::class)->penalty_fee,
                'type' => 'BOT',
                'description' => 'Boete prolongatie betaling #' . $prolongationPayment->id,
            ]);
        }

        return $this->success([
            'verschil' => $diffInDays,
            'member' => $memberId,
            'message' => 'Lidmaatschap geprolongeerd tot en met ' . $newExpiryDate->copy()->locale('nl')->isoFormat('LL'),
        ], 'geprolongeerd');
    }

    public function apiIndex(Request $request)
    {
        return null;
    }
}
