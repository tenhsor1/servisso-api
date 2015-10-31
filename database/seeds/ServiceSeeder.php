<?php

use Illuminate\Database\Seeder;

// import the Service model.
use App\Service;
use App\User;
use App\Branch;

// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class ServiceSeeder extends Seeder {

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
        $numUsers = User::all()->count();
        $numBranches = Branch::all()->count();

        for ($i=0; $i < 40; $i++) {
            Service::create(
            [
                'description'=>$faker->text(500),
                'branch_id'=>$faker->numberBetween(1,$numBranches),
                'user_id'=>$faker->numberBetween(1,$numUsers),
                'user_type'=>$faker->numberBetween(0,1)
            ]
        );
        }
    }

}