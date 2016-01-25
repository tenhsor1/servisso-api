<?php

use Illuminate\Database\Seeder;
use App\Category;

// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class CategorySeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a faker instance
        $faker = Faker::create();

        // For covering the users, we get the count from user model.
        // So that way the foreign key user_id won't give us any problems.

        for ($i=0; $i < 40; $i++) {
            Category::create(
                [
                    'name'=>$faker->text(45),
                    'description'=>$faker->text(500),
					'role_id'=>0,
					'role'=>0 

                ]
            );
        }
    }

}