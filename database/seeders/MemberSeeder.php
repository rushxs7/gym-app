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
            $eom = Carbon::today()->addMonthsWithoutOverflow(rand(-3,0));
            Member::create([
                'name' => $faker->firstName() . ' ' . $faker->lastName(),
                'address' => rand(0,1) ? $faker->address() : '',
                'email' => rand(0,1) ? $faker->email() : '',
                'phone' => $faker->phoneNumber(),
                'gender' => rand(0,1) ? 'male' : 'female',
                'end_of_membership' => $eom
            ]);
        }
    }
}
