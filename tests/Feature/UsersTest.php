<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('returns a paginated list of users', function () {
    $authUser = User::factory()->create([
        'email' => 'auth@example.com',
        'username' => 'authuser',
    ]);

    User::factory()->count(25)->create();

    $token = JWTAuth::fromUser($authUser);

    $response = $this->withToken($token)->getJson('/api/users?size=10&page=1');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.page_size', 10)
        ->assertJsonCount(10, 'data');
});

it('filters users by search query', function () {
    $authUser = User::factory()->create([
        'email' => 'auth@example.com',
        'username' => 'authuser',
    ]);

    $match = User::factory()->create([
        'name' => 'Special Person',
        'email' => 'special@example.com',
        'username' => 'specialperson',
    ]);

    User::factory()->count(5)->create();

    $token = JWTAuth::fromUser($authUser);

    $response = $this->withToken($token)->getJson('/api/users?q=Special');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonFragment([
            'email' => 'special@example.com',
            'username' => 'specialperson',
        ]);
});

it('shows a single user detail', function () {
    $authUser = User::factory()->create([
        'email' => 'auth@example.com',
        'username' => 'authuser',
    ]);

    $otherUser = User::factory()->create([
        'email' => 'other@example.com',
        'username' => 'otheruser',
    ]);

    $token = JWTAuth::fromUser($authUser);

    $response = $this->withToken($token)->getJson('/api/users/'.$otherUser->id);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'other@example.com')
        ->assertJsonPath('data.username', 'otheruser');
});
