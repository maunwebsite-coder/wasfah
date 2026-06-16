<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createCustomer();
        $this->createChef();
        $this->createAdmin();
    }

    private function createCustomer(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'user.demo@wasfah.test'],
            [
                'name' => 'مستخدم تجريبي',
                'password' => Hash::make('UserPass123!'),
                'role' => User::ROLE_CUSTOMER,
                'is_admin' => false,
                'chef_status' => null,
                'phone' => null,
            ]
        );

        $this->markEmailVerified($user);
    }

    private function createChef(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'chef.demo@wasfah.test'],
            [
                'name' => 'شيف تجريبي',
                'password' => Hash::make('ChefPass123!'),
                'role' => User::ROLE_CHEF,
                'chef_status' => User::CHEF_STATUS_APPROVED,
                'chef_approved_at' => now(),
                'phone' => '+966500000001',
                'instagram_url' => 'https://www.instagram.com/thedolci.jo/',
                'instagram_followers' => 1200,
                'youtube_url' => 'https://www.youtube.com/@demo-chef',
                'youtube_followers' => 2300,
                'chef_specialty_area' => 'food',
                'chef_specialty_description' => 'شيف تجريبي متخصص في وصفات المطبخ العربي والعالمي.',
            ]
        );

        $this->markEmailVerified($user);
    }

    private function createAdmin(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin.demo@wasfah.test'],
            [
                'name' => 'مسؤول تجريبي',
                'password' => Hash::make('AdminPass123!'),
                'role' => User::ROLE_ADMIN,
                'is_admin' => true,
                'chef_status' => null,
                'phone' => '+966500000000',
            ]
        );

        $this->markEmailVerified($user);
    }

    private function markEmailVerified(User $user): void
    {
        if (!$user->email_verified_at) {
            $user->forceFill([
                'email_verified_at' => now(),
                'last_login_at' => null,
                'last_login_ip' => null,
            ])->save();
        }
    }
}

