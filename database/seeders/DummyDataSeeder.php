<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(10)->create()->each(function ($user) {
            for ($i = 0; $i < rand(5, 15); $i++) {
                $user->activities()->create([
                    'points' => 20,
                    'performed_at' => now()->subDays(rand(0, 30))
                ]);
            }
        });
    }
}
