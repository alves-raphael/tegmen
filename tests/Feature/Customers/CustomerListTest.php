<?php

use App\Models\Customer;
use App\Models\User;

test('guests are redirected from customer list', function () {
    $this->get(route('customers.index'))->assertRedirect(route('login'));
});

test('authenticated users can view customer list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('customers.index'))
        ->assertOk();
});

test('users only see their own customers', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $ownCustomer = Customer::factory()->create(['user_id' => $user->id]);
    $otherCustomer = Customer::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->get(route('customers.index'));

    $response->assertSee($ownCustomer->name);
    $response->assertDontSee($otherCustomer->name);
});
