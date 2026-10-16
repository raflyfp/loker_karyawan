<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@snamedika.com')
            ->orWhere('username', 'admin')
            ->first();

        if ($user) {
            $user->update([
                'name' => 'Administrator SNA',
                'username' => 'admin',
                'email' => 'admin@snamedika.com',
                'password' => Hash::make('adminsna'),
            ]);
        } else {
            User::create([
                'name' => 'Administrator SNA',
                'username' => 'admin',
                'email' => 'admin@snamedika.com',
                'password' => Hash::make('adminsna'),
            ]);
        }
    }
}
