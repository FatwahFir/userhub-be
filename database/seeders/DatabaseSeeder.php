<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('Password123!');

        User::factory()->create([
            'username' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => $password,
            'role' => 'admin',
            'phone' => '+621234567890',
        ]);

        foreach ($this->demoUsers() as $attributes) {
            User::factory()->create(array_merge([
                'password' => $password,
            ], $attributes));
        }

        $roles = ['manager', 'staff', 'support'];

        foreach ($roles as $role) {
            User::factory()
                ->count(5)
                ->state(fn () => ['role' => $role])
                ->create();
        }

        User::factory()
            ->count(40)
            ->create();
    }

    /**
     * Demo users with deterministic data for QA.
     *
     * @return array<int, array<string, string|null>>
     */
    protected function demoUsers(): array
    {
        return [
            [
                'username' => 'jane_doe',
                'name' => 'Jane Doe',
                'email' => 'jane.doe@example.com',
                'role' => 'manager',
                'phone' => '+62111111111',
            ],
            [
                'username' => 'john_smith',
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'role' => 'staff',
                'phone' => '+62122222222',
            ],
            [
                'username' => 'ayu_putri',
                'name' => 'Ayu Putri',
                'email' => 'ayu.putri@example.com',
                'role' => 'support',
                'phone' => '+62133333333',
            ],
            [
                'username' => 'budi_santoso',
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'role' => 'user',
                'phone' => '+62144444444',
            ],
            [
                'username' => 'siti_hasanah',
                'name' => 'Siti Hasanah',
                'email' => 'siti.hasanah@example.com',
                'role' => 'user',
                'phone' => '+62155555555',
            ],
        ];
    }
}
