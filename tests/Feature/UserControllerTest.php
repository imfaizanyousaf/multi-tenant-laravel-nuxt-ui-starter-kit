<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login page when accessing users page', function (): void {
    $response = $this->get(route('users'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the users page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('users'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page): Assert => $page
        ->component('Users')
        ->has('users')
    );
});
