<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = env('ADMIN_PASSWORD');
        
        if (! $password) {
            $password = bin2hex(random_bytes(12));
            $this->command->warn("No ADMIN_PASSWORD set in .env — generated password: {$password}");
        }

        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@srokhhub.com')],
            [
                'name'     => 'SrokhHub Admin',
                'password' => bcrypt($password),
                'role'     => 'admin',
            ]
        );
    }
}
