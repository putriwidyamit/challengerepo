<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class WsUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Insert sample test data
        $users = [];
        for ($i = 1; $i <= 100; $i++) {
            // Vary data to test quality metrics
            $hasEmail = $i % 10 != 0; // 10% missing emails
            $hasPhone = $i % 5 != 0;  // 20% missing phones
            $hasBirthDate = $i % 8 != 0; // 12.5% missing birth dates
            $hasHobbies = $i % 10 != 0; // 10% null hobbies
            
            $users[] = [
                'user_id' => $i,
                'user_name' => 'user_' . $i,
                'full_name' => $faker->firstName . ' ' . $faker->lastName,
                'user_email' => $hasEmail ? 'user' . $i . '@example.com' : null,
                'msisdn' => $hasPhone ? '0812' . str_pad($i, 8, '0', STR_PAD_LEFT) : null,
                'birth_date' => $hasBirthDate ? $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d') : null,
                'hobbies' => $hasHobbies ? implode(', ', $faker->words(3)) : null,
                'status' => rand(-1, 2),
                'create_time' => Carbon::now()->subDays(rand(1, 365)),
                'update_time' => Carbon::now(),
                'last_login' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null,
            ];
        }

        // Add some edge cases for testing
        $users[] = [
            'user_id' => 101,
            'user_name' => 'komang_pipit',
            'full_name' => 'Komang Pipit',
            'user_email' => 'komang@email.com',
            'msisdn' => '081234567890',
            'birth_date' => '1990-01-15',
            'hobbies' => 'reading, coding 🎮, gaming',
            'status' => 1,
            'create_time' => Carbon::now()->subDays(100),
            'update_time' => Carbon::now(),
            'last_login' => Carbon::now()->subDays(5),
        ];

        // Email/Phone duplicate
        $users[] = [
            'user_id' => 102,
            'user_name' => 'komang_pipit_2',
            'full_name' => 'Komang Pipit Bali',
            'user_email' => 'komang@email.com', // Duplicate email (case insensitive)
            'msisdn' => '081234567890', // Duplicate phone
            'birth_date' => '1995-05-20',
            'hobbies' => 'sports, travel',
            'status' => 1,
            'create_time' => Carbon::now()->subDays(50),
            'update_time' => Carbon::now(),
            'last_login' => Carbon::now()->subDays(2),
        ];

        // Invalid email, malformed phone
        $users[] = [
            'user_id' => 103,
            'user_name' => 'test_user',
            'full_name' => 'Test User',
            'user_email' => 'invalid-email@test',
            'msisdn' => '123abc456',
            'birth_date' => '1800-01-01', // Impossible date
            'hobbies' => 'music, art!@#',
            'status' => 0,
            'create_time' => Carbon::now()->subDays(30),
            'update_time' => Carbon::now(),
            'last_login' => Carbon::now()->subDays(1),
        ];

        // Future birth date
        $users[] = [
            'user_id' => 104,
            'user_name' => 'future_user',
            'full_name' => 'Future User',
            'user_email' => 'future@example.com',
            'msisdn' => '089123456789',
            'birth_date' => Carbon::now()->addDays(100)->format('Y-m-d'),
            'hobbies' => null,
            'status' => 1,
            'create_time' => Carbon::now(),
            'update_time' => Carbon::now(),
            'last_login' => null,
        ];

        DB::table('ws_user')->insert($users);
    }
}

