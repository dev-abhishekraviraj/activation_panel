<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('users')->insert([
            'name'=>'smarttv-admin',
            'email'=>'smarttv-admin@gmail.com',
            'password'=> \Hash::make('smarttv-admin@gmail.com'),
            'is_admin'=> 0,
            'created_at'=>date('Y:m:d H:i:s'),
            'updated_at'=>date('Y:m:d H:i:s'),
        ]);
    }
}
