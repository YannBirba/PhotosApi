<?php

namespace Tests;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(TestCase::class, RefreshDatabase::class)->in('Feature', 'Unit');

/**
 * Method createGroup
 *
 * @return Group
 */
function createGroup(): Group
{
    return Group::factory()->create();
}

/**
 * Method createUser
 *
 * @param  Group  $group [explicite description]
 * @param  bool  $isAdmin [explicite description]
 * @return User
 */
function createUser(Group $group, bool $isAdmin = false): User
{
    $user = User::factory()->create([
        'group_id' => $group->id,
        'email' => "test$group->id@test.fr",
        'is_admin' => $isAdmin,
    ]);

    return $user;
}

/**
 * Method logIn
 *
 * @param  string  $email [explicite description]
 * @param  string  $password [explicite description]
 * @param  bool  $remember [explicite description]
 * @return TestResponse
 */
function login(string $email, string $password, bool $remember = false): TestResponse
{
    get('/sanctum/csrf-cookie');

    return post('/api/login', [
        'email' => $email,
        'password' => $password,
        'remember' => $remember,
    ]);
}

function register(User $user, ?int $group_id = null, ?string $name = null, ?string $email = null, ?string $password = null, ?string $password_confirmation = null, ?bool $is_admin = null): TestResponse
{
    return actingAs($user)
        ->post('/api/register', [
            'group_id' => $group_id,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password_confirmation,
            'is_admin' => $is_admin,
            'is_active' => true,
        ]);
}
