<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            [
                'full_name' => 'John Doe',
                'role_id' => 1,
                'username' => 'admin',
                'password' => Hash::make('password'),
                'email' => 'admin@gmail.com',
                'phone' => '0912457896',
                'address' => '123 Admin St',
                'is_active' => true,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'Jane Staff',
                'role_id' => 2,
                'username' => 'jane.staff',
                'password' => Hash::make('password'),
                'email' => 'staff@gmail.com',
                'phone' => '0987654321',
                'address' => '456 Staff Ave',
                'is_active' => true,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'Alice Customer',
                'role_id' => 3,
                'username' => 'alice.cust',
                'password' => Hash::make('password'),
                'email' => 'customer@gmail.com',
                'phone' => '0756875934',
                'address' => '789 Customer Rd',
                'is_active' => true,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
