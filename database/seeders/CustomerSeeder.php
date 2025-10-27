<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'user_id' => 3,
            'id_photo_path_front' => 'uploads/id/alice.jpg',
            'id_photo_path_back' => 'uploads/id/alice_back.jpg',
            'profile_photo_path' => 'uploads/profile/alice.jpg',
            'is_verified' => true,]);
    }
}
