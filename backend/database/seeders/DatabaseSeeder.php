<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        foreach (['admin', 'editor', 'clinician'] as $roleName) {
            Role::findOrCreate($roleName);
        }

        // Create admin user and assign role
        $adminEmail = env('ADMIN_EMAIL', 'admin@saliksic.com');
        $adminPassword = env('ADMIN_PASSWORD', 'changeme');

        $user = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Site Admin',
                'password' => Hash::make($adminPassword),
            ]
        );

        if (! $user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
