<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('returns authenticated user profile', function () {
    $user = User::factory()->create([
        'email' => 'profile@example.com',
        'username' => 'profileuser',
    ]);

    $token = JWTAuth::fromUser($user);

    $response = $this->withToken($token)->getJson('/api/me');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'profile@example.com')
        ->assertJsonPath('data.username', 'profileuser');
});

it('updates profile details including avatar upload', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'email' => 'old@example.com',
        'username' => 'oldusername',
    ]);

    $token = JWTAuth::fromUser($user);

    $response = $this->withToken($token)->post('/api/me', [
        '_method' => 'PUT',
        'name' => 'New Name',
        'email' => 'new@example.com',
        'username' => 'newusername',
        'phone' => '+1234567890',
        'avatar' => UploadedFile::fake()->image('avatar.jpg', 256, 256),
    ], ['Accept' => 'application/json']);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'new@example.com')
        ->assertJsonPath('data.username', 'newusername')
        ->assertJsonPath('data.phone', '+1234567890')
        ->assertJsonPath('data.avatar_url', fn ($value) => $value !== null);

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});
