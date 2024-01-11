<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Admin::count() === 0) {
            Admin::create([
                'username' => 'jkmdroid',
                'email' => 'jkmdroid@petdiaries.io',
                'password' => Hash::make('jkm@2pac')
            ]);
            $this->command->info('Admin user created successfully!');
        } else {
            $this->command->info('Admin user already exists, skipping creation.');
        }
    }
}
