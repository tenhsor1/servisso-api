<?php

use Illuminate\Database\Seeder;

// import the Call model.
use App\News;
use App\NewComment;
// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class NewCommentSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a faker instance
        $faker = Faker::create();

        // For covering the services, we get the count from service model.
        // So that way the foreign key service_id won't give us any problems.
        $numComments = News::all()->count();
        $userIds = 3;//default
        $typeIds = 5;//default
        for ($i=0; $i < 20; $i++) {
            NewComment::create(
                [
                    'new_id'=>$faker->numberBetween(1,$numComments),
                    'user_id'=>$faker->numberBetween(1,$userIds),
                    'comment'=>$faker->text(145),
                    'user_type'=>$faker->numberBetween(0,1)

                ]
            );
        }
    }

}