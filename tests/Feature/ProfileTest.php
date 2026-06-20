<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings')
        ->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/settings', ['name' => 'Test User'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('settings'));

    $this->assertSame('Test User', $user->refresh()->name);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $originalEmail      = $user->email;
    $originalVerifiedAt = $user->email_verified_at;

    $this->actingAs($user)
        ->put('/settings', ['name' => 'New Name'])
        ->assertSessionHasNoErrors();

    $user->refresh();
    $this->assertSame($originalEmail, $user->email);
    $this->assertEquals($originalVerifiedAt, $user->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/account')
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('account deletion requires authentication', function () {
    $this->delete('/account')
        ->assertRedirect(route('login'));
});
