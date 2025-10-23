<?php

use App\Models\User;
use function Pest\Laravel\{actingAs, get, post};

it('can login with username', function () {
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $response = post('/login', [
        'email' => 'testuser', // Using username in email field
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->username)->toBe('testuser');
});

it('can login with email', function () {
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $response = post('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->email)->toBe('test@example.com');
});

it('cannot login with invalid username', function () {
    User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $response = post('/login', [
        'email' => 'wronguser',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
    expect(auth()->check())->toBeFalse();
});

it('cannot login with invalid password', function () {
    User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $response = post('/login', [
        'email' => 'testuser',
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors(['email']);
    expect(auth()->check())->toBeFalse();
});

it('can register with username', function () {
    $response = post('/register', [
        'name' => 'Test User',
        'username' => 'newtestuser',
        'email' => 'newtest@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->username)->toBe('newtestuser');
    expect(auth()->user()->email)->toBe('newtest@example.com');
});

it('cannot register with duplicate username', function () {
    User::factory()->create([
        'username' => 'existinguser',
        'email' => 'existing@example.com',
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $response = post('/register', [
        'name' => 'Test User',
        'username' => 'existinguser',
        'email' => 'newtest@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['username']);
    expect(auth()->check())->toBeFalse();
});

it('cannot register with duplicate email', function () {
    User::factory()->create([
        'username' => 'existinguser',
        'email' => 'existing@example.com',
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $response = post('/register', [
        'name' => 'Test User',
        'username' => 'newuser',
        'email' => 'existing@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
    expect(auth()->check())->toBeFalse();
});