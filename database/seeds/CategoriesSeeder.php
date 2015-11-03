<?php

use Illuminate\Database\Seeder;
use App\Categories;

// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class CategoriesSeeder extends Seeder {

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
            Categories::create(
                [
                    'name'=>$faker->text(45),
                    'description'=>$faker->text(500)

                ]
            );
        }
    }

}