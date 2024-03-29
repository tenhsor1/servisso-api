<?php

use Illuminate\Database\Seeder;

// import the User model.
use App\Guest;

// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class GuestSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a faker instance
        $faker = Faker::create();

        // Creamos un bucle para cubrir 5 fabricantes:
        for ($i=0; $i < 50; $i++) {
            Guest::create(
                [
                    'email'=>$faker->email,
                    'name'=>$faker->firstName,
                    'phone'=>$faker->numberBetween($min = 10000000, $max = 99999999),
                    'address'=>$faker->optional()->address,
                    'zipcode'=>$faker->optional()->postcode
                ]
            );
        }
    }

}