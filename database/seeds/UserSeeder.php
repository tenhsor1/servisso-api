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
		$stateIds = 32;//default;
        $countryIds = 1;//default
        // Creamos un bucle para cubrir 5 fabricantes:
        User::create([
            'id' => 0,
            'email' => 'notaccount@servisso.com.mx',
            'password' => 'Jisk3sl20sll32pñ0',
            'name' => 'NOT',
            'lastname' => 'ACCOUNT',
            'phone' => '0000000000',
            'address' => 'NONE',
            'zipcode' => '00000',
            'state_id' => 1
        ]);

        User::create(
            [
                'email'=>'ricardo.romo.ramirez@gmail.com',
                'password'=>'Testing123',
                'name'=>'Ricardo',
                'lastname'=>'Romo',
                'phone'=>$faker->numberBetween($min = 10000000, $max = 99999999),
                'address'=>$faker->optional()->address,
				'state_id'=>$faker->numberBetween(1,$stateIds),
                'zipcode'=>$faker->optional()->postcode,
                'enabled_companies' => 1,
                'confirmed' => true
            ]
        );

        User::create(
            [
                'email'=>'ricardo.romo.ramirez+u1@gmail.com',
                'password'=>'Testing123',
                'name'=>'Rick U',
                'lastname'=>'Acc',
                'phone'=>$faker->numberBetween($min = 10000000, $max = 99999999),
                'address'=>$faker->optional()->address,
                'state_id'=>$faker->numberBetween(1,$stateIds),
                'zipcode'=>$faker->optional()->postcode,
                'enabled_companies' => 1,
                'confirmed' => true
            ]
        );

        User::create(
            [
                'email'=>'ricardo.romo.ramirez+100@gmail.com',
                'password'=>'Testing123',
                'name'=>'Ricardo',
                'lastname'=>'Romo',
                'phone'=>$faker->numberBetween($min = 10000000, $max = 99999999),
                'address'=>$faker->optional()->address,
                'state_id'=>$faker->numberBetween(1,$stateIds),
                'zipcode'=>$faker->optional()->postcode
            ]
        );

        User::create(
            [
                'email'=>'makoasoft@gmail.com',
                'password'=>'Makoa123',
                'name'=>$faker->firstName('male'), // de 9 dígitos como máximo.
                'lastname'=>$faker->lastName,
                'phone'=>$faker->numberBetween($min = 10000000, $max = 99999999),
                'address'=>$faker->optional()->address,
				'state_id'=>$faker->numberBetween(1,$stateIds),
                'zipcode'=>$faker->optional()->postcode
            ]
        );

        User::create(
            [
                'email'=>'ernesto.soft45@gmail.com',
                'password'=>'Ernesto123',
                'name'=>$faker->firstName('male'), // de 9 dígitos como máximo.
                'lastname'=>$faker->lastName,
                'phone'=>$faker->numberBetween($min = 10000000, $max = 99999999),
                'address'=>$faker->optional()->address,
				'state_id'=>$faker->numberBetween(1,$stateIds),
                'zipcode'=>$faker->optional()->postcode
            ]
        );

        User::create(
            [
                'email'=>'radames.ramirez.perez@gmail.com',
                'password'=>'Radames123',
                'name'=>$faker->firstName('male'), // de 9 dígitos como máximo.
                'lastname'=>$faker->lastName,
                'phone'=>$faker->numberBetween($min = 10000000, $max = 99999999),
                'address'=>$faker->optional()->address,
				'state_id'=>$faker->numberBetween(1,$stateIds),
                'zipcode'=>$faker->optional()->postcode
            ]
        );

    }

}