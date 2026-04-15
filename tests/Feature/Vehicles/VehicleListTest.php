<?php

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;

test('guests are redirected from vehicle list', function () {
    $customer = Customer::factory()->create();

    $this->get(route('vehicles.index', $customer))->assertRedirect(route('login'));
});

test('authenticated users can view vehicle list for their customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('vehicles.index', $customer))
        ->assertOk();
});

test('users cannot view vehicles for another users customer', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->get(route('vehicles.index', $customer))
        ->assertForbidden();
});

test('vehicle list shows customer name and email in heading', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('vehicles.index', $customer))
        ->assertSee($customer->name)
        ->assertSee($customer->email);
});

test('vehicle list shows model brand and license plate', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $this->actingAs($user)
        ->get(route('vehicles.index', $customer))
        ->assertSee($vehicle->model)
        ->assertSee($vehicle->brand)
        ->assertSee($vehicle->license_plate);
});
