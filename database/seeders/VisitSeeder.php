<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VisitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $membersArray = Member::get(['id'])->pluck('id');
        $membersArrayLength = count($membersArray);

        for ($i=0; $i < 100; $i++) {
            $arrival = Carbon::now()->subDays(rand(0,20))->subHours(rand(0,4))->toDateTimeString();
            $departure = Carbon::parse($arrival)->addHours(rand(1,3));
            Visit::create([
                'member_id' => $membersArray[rand(0, $membersArrayLength - 1)],
                'time_of_arrival' => $arrival,
                'time_of_departure' => $departure,
            ]);
            unset($arrival);
            unset($departure);
        }
    }
}
