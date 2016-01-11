<?php

use Illuminate\Database\Seeder;

// import the Service model.
use App\Tag;
use App\Category;

// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class TagSeeder extends Seeder {

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
        $categoryIds = 5;//default

        if(Schema::hasTable('category'))
            $categoryIds = Category::all()->count();




        for ($i=0; $i < 40; $i++) {
            Tag::create(
                [
                    'name'=>$faker->text(50),
                    'description'=>$faker->text(500),
                    'category_id'=>$faker->numberBetween(1,$categoryIds)
                ]
            );
        }
    }

}