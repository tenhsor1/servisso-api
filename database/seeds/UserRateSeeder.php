<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Service;
use App\Partner;
use App\UserRate;
class UserRateSeeder extends Seeder {

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
        $userIds = 5;//default

        if(Schema::hasTable('services'))
            $serviceIds = Service::all()->count();

        if(Schema::hasTable('users'))
            $userIds = Partner::all()->count() - 1;



        for ($i=0; $i < 40; $i++) {
            UserRate::create(
                [
                   'service_id'=>$faker->numberBetween(1,$serviceIds),
                    'rate'=>$faker->randomFloat(),
                    'comment'=>$faker->text(500),
                    'user_id'=>$faker->numberBetween(1,$userIds)
                ]
            );
        }
    }

}