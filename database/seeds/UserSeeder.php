<?php

use Illuminate\Database\Seeder;

// import the User model.
use App\User;

// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class UserSeeder extends Seeder {

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
        User::create(
            [
                'email'=>'ricardo.romo.ramirez@gmail.com',
                'password'=>bcrypt('Testing123'),
                'name'=>'Ricardo',
                'last_name'=>'Romo',
                'phone'=>$faker->phoneNumber,
                'address'=>$faker->optional()->address,
                'zipcode'=>$faker->optional()->postcode
            ]
        );

        User::create(
            [
                'email'=>'makoasoft@gmail.com',
                'password'=>bcrypt('Makoa123'),
                'name'=>$faker->firstName('male'), // de 9 dígitos como máximo.
                'last_name'=>$faker->lastName,
                'phone'=>$faker->phoneNumber,
                'address'=>$faker->optional()->address,
                'zipcode'=>$faker->optional()->postcode
            ]
        );

        User::create(
            [
                'email'=>'ernesto.soft45@gmail.com',
                'password'=>bcrypt('Ernesto123'),
                'name'=>$faker->firstName('male'), // de 9 dígitos como máximo.
                'last_name'=>$faker->lastName,
                'phone'=>$faker->phoneNumber,
                'address'=>$faker->optional()->address,
                'zipcode'=>$faker->optional()->postcode
            ]
        );

        User::create(
            [
                'email'=>'radames.ramirez.perez@gmail.com',
                'password'=>bcrypt('Radames123'),
                'name'=>$faker->firstName('male'), // de 9 dígitos como máximo.
                'last_name'=>$faker->lastName,
                'phone'=>$faker->phoneNumber,
                'address'=>$faker->optional()->address,
                'zipcode'=>$faker->optional()->postcode
            ]
        );

    }

}