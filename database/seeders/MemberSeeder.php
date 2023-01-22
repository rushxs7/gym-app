<?php

namespace Database\Seeders;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        for ($i=0; $i < 10; $i++) {
            $eom = Carbon::today()->addMonths(rand(-3,0));
            Member::create([
                'rfid_code' => $faker->uuid(),
                'name' => $faker->name(),
                'address' => rand(0,1) ? $faker->address() : '',
                'email' => rand(0,1) ? $faker->email() : '',
                'phone' => $faker->phoneNumber(),
                'phone2' => rand(0,1) ? $faker->phoneNumber() : '',
                'gender' => rand(0,1) ? 'male' : 'female',
                'active' => rand(0,1),
                'end_of_membership' => $eom
            ]);
        }
    }
}
