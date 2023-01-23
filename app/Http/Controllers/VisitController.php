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
            ->when(request('timespan'), function ($query) {
                switch (request('timespan')) {
                    case 'today':
                        $query->whereDate('time_of_arrival', Carbon::today()->toDateString());
                        break;
                    case 'yesterday':
                        $query->whereDate('time_of_arrival', Carbon::yesterday()->toDateString());
                        break;
                    case 'thisweek':
                        $query->whereBetween('time_of_arrival', [Carbon::today()->startOfWeek()->toDateString(), Carbon::today()->endOfWeek()->toDateString()]);
                        break;
                    case 'thismonth':
                        $query->whereMonth('time_of_arrival', Carbon::today()->month);
                        break;
                }
            })
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
