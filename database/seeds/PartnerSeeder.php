<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Partner;
use App\State;
use App\Country;
use App\Plan;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		// Create a faker instance
		$faker = Faker::create();

		$stateIds = 5;//default
		$countryIds = 5;//default
		$planIds = 5;//default

		//Verify if the tables exists in the database to retrieve the data
		if(Schema::hasTable('states'))
			$stateIds = State::all()->count();

		if(Schema::hasTable('countries'))
			$countryIds = Country::all()->count();

		if(Schema::hasTable('plans'))
			$planIds = Plan::all()->count();

		Partner::create([
			'email' => $faker->email,
			'password' => $faker->password,
			'name' => $faker->firstName,
			'lastname' => $faker->lastName,
			'birthdate' => rand(183020177,1445324177),//1975 - 2015
			'phone' => str_replace('.','-',$faker->phoneNumber),
			'address' => $faker->address,
			'zipcode' => rand(10000,99999),
			'state_id' => $faker->numberBetween(1,$stateIds),
			'country_id' => $faker->numberBetween(1,$countryIds),
			'status' => $faker->text(30),
			'plan_id' => $faker->numberBetween(1,$planIds)
		]);

		Partner::create([
			'email' => $faker->email,
			'password' => $faker->password,
			'name' => $faker->firstName,
			'lastname' => $faker->lastName,
			'birthdate' => rand(183020177,1445324177),//1975 - 2015
			'phone' => str_replace('.','-',$faker->phoneNumber),
			'address' => $faker->address,
			'zipcode' => rand(10000,99999),
			'state_id' => $faker->numberBetween(1,$stateIds),
			'country_id' => $faker->numberBetween(1,$countryIds),
			'status' => $faker->text(30),
			'plan_id' => $faker->numberBetween(1,$planIds)
		]);

		Partner::create([
			'email' => $faker->email,
			'password' => $faker->password,
			'name' => $faker->firstName,
			'lastname' => $faker->lastName,
			'birthdate' => rand(183020177,1445324177),//1975 - 2015
			'phone' => str_replace('.','-',$faker->phoneNumber),
			'address' => $faker->address,
			'zipcode' => rand(10000,99999),
			'state_id' => $faker->numberBetween(1,$stateIds),
			'country_id' => $faker->numberBetween(1,$countryIds),
			'status' => $faker->text(30),
			'plan_id' => $faker->numberBetween(1,$planIds)
		]);

		Partner::create([
			'email' => $faker->email,
			'password' => $faker->password,
			'name' => $faker->firstName,
			'lastname' => $faker->lastName,
			'birthdate' => rand(183020177,1445324177),//1975 - 2015
			'phone' => str_replace('.','-',$faker->phoneNumber),
			'address' => $faker->address,
			'zipcode' => rand(10000,99999),
			'state_id' => $faker->numberBetween(1,$stateIds),
			'country_id' => $faker->numberBetween(1,$countryIds),
			'status' => $faker->text(30),
			'plan_id' => $faker->numberBetween(1,$planIds)
		]);

		Partner::create([
			'email' => $faker->email,
			'password' => $faker->password,
			'name' => $faker->firstName,
			'lastname' => $faker->lastName,
			'birthdate' => rand(183020177,1445324177),//1975 - 2015
			'phone' => str_replace('.','-',$faker->phoneNumber),
			'address' => $faker->address,
			'zipcode' => rand(10000,99999),
			'state_id' => $faker->numberBetween(1,$stateIds),
			'country_id' => $faker->numberBetween(1,$countryIds),
			'status' => $faker->text(30),
			'plan_id' => $faker->numberBetween(1,$planIds)
		]);

    }
}
