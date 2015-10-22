<?php

use Illuminate\Database\Seeder;

// import the Call model.
use App\News;
use App\Admin;

// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class NewSeeder extends Seeder {

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
        $numAdmins = Admin::all()->count();
        for ($i=0; $i < 20; $i++) {
            News::create(
                [
                    'admin_id'=>$faker->unique()->numberBetween(1,$numAdmins),
                    'title'=>$faker->text(45),
                    'content'=>$faker->text(1545),
                    'image'=>$faker->text(145),
                    'status'=>$faker->randomNumber(2)

                ]
            );
        }
    }

}