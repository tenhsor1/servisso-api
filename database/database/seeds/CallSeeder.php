<?php

use Illuminate\Database\Seeder;

// import the Call model.
use App\Call;
use App\Service;

// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class CallSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a faker instance
        $faker = Faker::create();

        $status = array('ANSWERED', 'NOT_ANSWERED', 'HANGED');
        $answered = array('Y', 'N');

        // For covering the services, we get the count from service model.
        // So that way the foreign key service_id won't give us any problems.
        $numServices = Service::all()->count();

        for ($i=0; $i < 40; $i++) {
            Call::create(
            [
                'length'=>$faker->randomNumber(3),
                'url'=>$faker->unique()->word,
                'status'=>$faker->randomElement($status),
                'to'=>$faker->phoneNumber,
                'from'=>$faker->phoneNumber,
                'answered'=>$faker->randomElement($answered),
                'service_id'=>$faker->numberBetween(1,$numServices)
            ]
        );
        }
    }

}