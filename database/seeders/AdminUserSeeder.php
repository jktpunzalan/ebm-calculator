<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Role::findOrCreate('admin', 'web');
            Role::findOrCreate('editor', 'web');
            Role::findOrCreate('clinician', 'web');

            $admin = User::firstOrCreate(
                ['email' => env('ADMIN_EMAIL', 'admin@saliksic.com')],
                [
                    'name' => 'Site Admin',
                    'password' => Hash::make(env('ADMIN_PASSWORD')),
                    'role' => 'admin',
                ]
            );

            if (!$admin->hasRole('admin')) {
                $admin->assignRole('admin');
            }
        });
    }
}
