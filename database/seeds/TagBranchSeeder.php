<?php

use Illuminate\Database\Seeder;

// import the Service model.
use App\Branch;
use App\Tag;
use App\TagBranch;

// Use faker for generate random strings.
// Faker information: https://github.com/fzaninotto/Faker
use Faker\Factory as Faker;

class TagBranchSeeder extends Seeder {

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
        $branchIds = 5;//default
        $tagIds = 5;//default

        if(Schema::hasTable('services'))
            $branchIds = Branch::all()->count();

        if(Schema::hasTable('tag'))
            $tagIds = Tag::all()->count();



        for ($i=0; $i < 40; $i++) {
            TagBranch::create(
                [
                    'tag_id'=>$faker->numberBetween(1,$branchIds),
                    'branch_id'=>$faker->numberBetween(1,$tagIds)
                ]
            );
        }
    }

}