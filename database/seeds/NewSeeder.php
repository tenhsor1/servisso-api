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
        $numAdmins= 5;//default
        if(Schema::hasTable('admins'))
        $numAdmins = Admin::all()->count();
        for ($i=0; $i < 20; $i++) {
            News::create(
                [
                    'admin_id'=>$faker->numberBetween(1,$numAdmins),
                    'title'=>$faker->text(45),
                    'content'=>$faker->text(1545),
                    'image'=>$faker->text(145),
                    'status'=>$faker->numberBetween(0,1),  
					'role_id'=>0,
					'role'=>0 

                ]
            );
        }
    }

}