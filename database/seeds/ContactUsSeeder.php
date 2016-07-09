<?php

use Illuminate\Database\Seeder;
use App\ContactUs;
use Faker\Factory as Faker;

class ContactUsSeeder extends Seeder {

  
    public function run()
    {
        // Create a faker instance
        $faker = Faker::create();

        // For covering the services, we get the count from service model.
        // So that way the foreign key service_id won't give us any problems.
        for ($i=0; $i < 20; $i++) {
            ContactUs::create(
                [
                    'email' => $faker->unique()->email,
                    'name'=> $faker->text(50),
                    'comment'=>$faker->text(300),
                ]
            );
        }
    }

}