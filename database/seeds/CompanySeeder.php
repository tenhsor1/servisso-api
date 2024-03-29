<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Category;
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

		$userIds = 5;//default;
		$categoryIds = 5;//default

		//Verify if the tables exists in the database to retrieve the data
		if(Schema::hasTable('users'))
			$userIds = User::all()->count() - 1;

		if(Schema::hasTable('categories'))
			$categoryIds = Category::all()->count();

		Company::create([
			'user_id' => 1,
			'name' => $faker->company,
			'description' => $faker->text(100),
			'category_id' => 1,
		]);

		Company::create([
			'user_id' => $faker->numberBetween(1,$userIds),
			'name' => $faker->company,
			'description' => $faker->text(100),
			'category_id' => $faker->numberBetween(1,$categoryIds),
		]);

		Company::create([
			'user_id' => $faker->numberBetween(1,$userIds),
			'name' => $faker->company,
			'description' => $faker->text(100),
			'category_id' => $faker->numberBetween(1,$categoryIds),
		]);

		Company::create([
			'user_id' => $faker->numberBetween(1,$userIds),
			'name' => $faker->company,
			'description' => $faker->text(100),
			'category_id' => $faker->numberBetween(1,$categoryIds),
		]);

		Company::create([
			'user_id' => $faker->numberBetween(1,$userIds),
			'name' => $faker->company,
			'description' => $faker->text(100),
			'category_id' => $faker->numberBetween(1,$categoryIds),
		]);

		Company::create([
			'user_id' => $faker->numberBetween(1,$userIds),
			'name' => $faker->company,
			'description' => $faker->text(100),
			'category_id' => $faker->numberBetween(1,$categoryIds),
		]);
	}
}
