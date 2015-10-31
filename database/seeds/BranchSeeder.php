<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Branch;
use App\Company;

class BranchSeeder extends Seeder
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

		$companyIds = 5;//default
		$stateIds = 5;//default

		//Verify if the tables exists in the database to retrieve the data
		if(Schema::hasTable('companies'))
			$companyIds = Company::all()->count();

		if(Schema::hasTable('states'))
			$stateIds = State::all()->count();			

		for($i = 0;$i < $stateIds;$i++){
			
			DB::table('branches')->insert([
				'company_id' => $faker->numberBetween(1,$companyIds),
				'address' => $faker->address,
				'phone' => $faker->phoneNumber,
				'latitude' => $faker->latitude,
				'longitude' => $faker->longitude,
				'state_id' => $faker->numberBetween(1,$stateIds),
				'schedule' => $faker->text(50)
			]);
			
		}
		
		/*Branch::create(
			[
				'company_id' => $faker->numberBetween(1,$companyIds),
				'address' => $faker->address,
				'phone' => $faker->phoneNumber,
				'latitude' => $faker->latitude,
				'longitude' => $faker->longitude,
				'state_id' => $faker->numberBetween(1,$stateIds),
				'schedule' => $faker->text(50)
			]
		);*/

    }
}
