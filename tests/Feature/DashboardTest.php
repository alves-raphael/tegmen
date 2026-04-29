<?php

use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('birthdays widget shows customers with birthday in current month', function () {
    $user = User::factory()->create();

    $birthdayThisMonth = Customer::factory()->create([
        'user_id' => $user->id,
        'birth_date' => now()->startOfMonth()->addDays(4)->subYears(30),
    ]);

    $birthdayOtherMonth = Customer::factory()->create([
        'user_id' => $user->id,
        'birth_date' => now()->addMonths(1)->subYears(25),
    ]);

    Livewire::actingAs($user)
        ->test('pages::dashboard.birthdays-of-month')
        ->assertSee($birthdayThisMonth->name)
        ->assertDontSee($birthdayOtherMonth->name);
});

test('birthdays widget only shows the authenticated user\'s customers', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $ownCustomer = Customer::factory()->create([
        'user_id' => $user->id,
        'birth_date' => now()->startOfMonth()->subYears(28),
    ]);

    $otherCustomer = Customer::factory()->create([
        'user_id' => $otherUser->id,
        'birth_date' => now()->startOfMonth()->subYears(28),
    ]);

    Livewire::actingAs($user)
        ->test('pages::dashboard.birthdays-of-month')
        ->assertSee($ownCustomer->name)
        ->assertDontSee($otherCustomer->name);
});

test('birthdays widget shows empty state when no birthdays this month', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::dashboard.birthdays-of-month')
        ->assertSee(__('Nenhum aniversariante este mês.'));
});