<?php

use Illuminate\Database\Seeder;
use App\Partner;
use App\Categories;
use App\Company;
use Faker\Factory as Faker;

class CompanySeeder extends Seeder
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

		$partnerIds = 5;//default;
		$categoryIds = 5;//default

		//Verify if the tables exists in the database to retrieve the data
		if(Schema::hasTable('companies'))
			$partnerIds = Partner::all()->count();

		if(Schema::hasTable('categories'))
			$categoryIds = Categories::all()->count();

		Company::create([
			'partner_id' => $faker->numberBetween(1,$partnerIds),
			'name' => $faker->company,
			'description' => $faker->text(100),
			'category_id' => $faker->numberBetween(1,$categoryIds),
		]);

		Company::create([
			'partner_id' => $faker->numberBetween(1,$partnerIds),
			'name' => $faker->company,
			'description' => $faker->text(100),
			'category_id' => $faker->numberBetween(1,$categoryIds),
		]);

		Company::create([
			'partner_id' => $faker->numberBetween(1,$partnerIds),
			'name' => $faker->company,
			'description' => $faker->text(100),
			'category_id' => $faker->numberBetween(1,$categoryIds),
		]);

		Company::create([
			'partner_id' => $faker->numberBetween(1,$partnerIds),
			'name' => $faker->company,
			'description' => $faker->text(100),
			'category_id' => $faker->numberBetween(1,$categoryIds),
		]);

		Company::create([
			'partner_id' => $faker->numberBetween(1,$partnerIds),
			'name' => $faker->company,
			'description' => $faker->text(100),
			'category_id' => $faker->numberBetween(1,$categoryIds),
		]);
	}
}
