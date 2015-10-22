<?php

use Illuminate\Database\Seeder;

// import the Call model.
use App\Admin;
// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class AdminSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a faker instance
        $faker = Faker::create();


        $stateIds = 5;//default;
        $countryIds = 5;//default
        $roleIds = 5;//default;

        Admin::create(
            [
                'email'=>'radames.ramirez.perez@gmail.com',
                'password'=>'rada.123',
                'name'=>'Radames',
                'last_name'=>'Ramirez',
                'phone'=>$faker->phoneNumber,
                'address'=>$faker->optional()->address,
                'zipcode'=>$faker->postcode,
                'state_id'=>$faker->numberBetween(1,$stateIds),
                'country_id'=>$faker->numberBetween(1,$countryIds),
                'role_id'=>$faker->numberBetween(1,$roleIds)
            ]
        );

        Admin::create(
            [
                'email'=>'ernesto.noriega@gmail.com',
                'password'=>'neto.123',
                'name'=>'Ernesto',
                'last_name'=>'Noriega',
                'phone'=>$faker->phoneNumber,
                'address'=>$faker->address,
                'zipcode'=>$faker->postcode,
                'state_id'=>$faker->numberBetween(1,$stateIds),
                'country_id'=>$faker->numberBetween(1,$countryIds),
                'role_id'=>$faker->numberBetween(1,$roleIds)
            ]
        );

        Admin::create(
            [
                'email'=>'rodrigo.gutierrez@gmail.com',
                'password'=>'rongo.123',
                'name'=>'Rodrigo',
                'last_name'=>'Gutierrez',
                'phone'=>$faker->phoneNumber,
                'address'=>$faker->address,
                'zipcode'=>$faker->postcode,
                'state_id'=>$faker->numberBetween(1,$stateIds),
                'country_id'=>$faker->unique()->numberBetween(1,$countryIds),
                'role_id'=>$faker->unique()->numberBetween(1,$roleIds)
            ]
        );
    }

}