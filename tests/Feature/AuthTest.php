<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('registers a new user and returns a token', function () {
    Storage::fake('public');

    $response = $this->postJson('/api/auth/register', [
        'username' => 'john_doe',
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'phone' => '+15555550123',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'john@example.com')
        ->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'user' => [
                    'id',
                    'username',
                    'email',
                    'name',
                    'role',
                ],
            ],
        ]);

    expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
});

it('logs in with username credentials', function () {
    $user = User::factory()->create([
        'username' => 'janedoe',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'username' => 'janedoe',
        'password' => 'Password123!',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.id', $user->id);
});

it('rejects invalid login attempts', function () {
    User::factory()->create([
        'username' => 'janedoe',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'username' => 'janedoe',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
});

it('dispatches password reset notification', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'someone@example.com',
    ]);

    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    Notification::assertSentTo($user, ResetPassword::class);
});
