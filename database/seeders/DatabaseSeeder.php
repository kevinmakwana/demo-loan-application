<?php

namespace Database\Seeders;
use Carbon\Carbon;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('users')->delete();
        $adminUser = [
            'id' => 1,
            'name'=>'admin',
            'email' => 'admin@mailinator.com',
            'password' => bcrypt('admin123'),
            'is_admin' =>1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        \DB::table('users')->insert($adminUser);
    }
}
