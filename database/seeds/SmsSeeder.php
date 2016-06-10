<?php

use Illuminate\Database\Seeder;

// import the Call model.
use App\Sms;
use App\Service;

// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class SmsSeeder extends Seeder {

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
        $numServices = Service::all()->count();
        for ($i=0; $i < 40; $i++) {
            Sms::create(
                [
                    'message'=>$faker->text(200),
                    'to'=>$faker->e164PhoneNumber,
                    'service_id'=>$faker->unique()->numberBetween(1,$numServices)
                ]
            );
        }
    }

}