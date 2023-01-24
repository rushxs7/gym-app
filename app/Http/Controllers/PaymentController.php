<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::when(request('searchquery'), function ($query) {
                $query->where('id', request('searchquery'))
                    ->orWhere('type', 'like', '%' . request('searchquery') . '%')
                    ->orWhere('description', 'like', '%' . request('searchquery') . '%')
                    ->orWhereHas('members', function (Builder $query) {
                        $query->where('name', 'like', '%' . request('searchquery') . '%');
                    });
            })
            ->when(request('memberId'), function ($query) {
                $query->where('member_id', request('memberId'));
            })
            ->when(request('timespan'), function ($query) {
                switch (request('timespan')) {
                    case 'today':
                        $query->whereDate('created_at', Carbon::today()->toDateString());
                        break;
                    case 'yesterday':
                        $query->whereDate('created_at', Carbon::yesterday()->toDateString());
                        break;
                    case 'thisweek':
                        $query->whereBetween('created_at', [Carbon::today()->startOfWeek()->startOfDay()->toDateTimeString(), Carbon::today()->endOfWeek()->endOfDay()->toDateTimeString()]);
                        break;
                    case 'thismonth':
                        $query->whereMonth('created_at', Carbon::today()->month);
                        break;
                    case 'custom':
                        $query->whereBetween('created_at', [
                                Carbon::parse(request('startFilter'))->startOfDay()->toDateTimeString(),
                                Carbon::parse(request('endFilter'))->endOfDay()->toDateTimeString()
                            ]);
                        break;
                }
            })
            ->orderBy('created_at', 'asc')
            ->with(['members'])
            ->paginate(15);

        return view('payments', [
            'payments' => $payments
        ]);
    }
}
