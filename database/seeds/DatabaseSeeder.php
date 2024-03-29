<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(UserSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(BranchSeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(CallSeeder::class);
        $this->call(SmsSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(NewSeeder::class);
        $this->call(NewCommentSeeder::class);
        $this->call(TagSeeder::class);
        $this->call(TagBranchSeeder::class);
        $this->call(GuestSeeder::class);

        Model::reguard();
    }
}
