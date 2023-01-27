<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $visits = Visit::when(request('searchquery'), function ($query) {
            $query->where('id', request('searchquery'))
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
                        $query->whereDate('time_of_arrival', Carbon::today()->toDateString());
                        break;
                    case 'yesterday':
                        $query->whereDate('time_of_arrival', Carbon::yesterday()->toDateString());
                        break;
                    case 'thisweek':
                        $query->whereBetween('time_of_arrival', [Carbon::today()->startOfWeek()->startOfDay()->toDateTimeString(), Carbon::today()->endOfWeek()->endOfDay()->toDateTimeString()]);
                        break;
                    case 'thismonth':
                        $query->whereMonth('time_of_arrival', Carbon::today()->month);
                        break;
                    case 'custom':
                        $query->whereBetween('time_of_arrival', [
                                Carbon::parse(request('startFilter'))->startOfDay()->toDateTimeString(),
                                Carbon::parse(request('endFilter'))->endOfDay()->toDateTimeString()
                            ]);
                        break;
                }
            })
            ->orderBy('time_of_arrival', 'desc')
            ->with(['members'])
            ->paginate(15);

        return view('visits', [
            'visits' => $visits
        ]);
    }

    public function store(Request $request)
    {
        // code...
    }
}
