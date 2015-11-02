<?php

use Illuminate\Database\Seeder;

// import the Service model.
use App\Service;
use App\PartnerRate;

// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class PartnerRateSeeder extends Seeder {

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
        $serviceIds = 5;//default

        if(Schema::hasTable('services'))
            $serviceIds = Service::all()->count();



        for ($i=0; $i < 40; $i++) {
            PartnerRate::create(
                [
                    'service_id'=>$faker->numberBetween(1,$serviceIds),
                    'rate'=>$faker->randomFloat(),
                    'comment'=>$faker->text(500),

                ]
            );
        }
    }

}