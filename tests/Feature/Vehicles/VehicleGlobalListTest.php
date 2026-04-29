<?php

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

test('guests are redirected from global vehicle list', function () {
    $this->get(route('vehicles.list'))->assertRedirect(route('login'));
});

test('authenticated users can access global vehicle list', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('vehicles.list'))
        ->assertOk();
});

test('global vehicle list only shows the authenticated user\'s vehicles', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $myCustomer = Customer::factory()->create(['user_id' => $user->id]);
    $myVehicle = Vehicle::factory()->create(['customer_id' => $myCustomer->id]);

    $otherCustomer = Customer::factory()->create(['user_id' => $other->id]);
    $otherVehicle = Vehicle::factory()->create(['customer_id' => $otherCustomer->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.list')
        ->assertSee($myVehicle->license_plate)
        ->assertDontSee($otherVehicle->license_plate);
});

test('search filters by license plate', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    $match = Vehicle::factory()->create(['customer_id' => $customer->id, 'license_plate' => 'ABC1234']);
    $noMatch = Vehicle::factory()->create(['customer_id' => $customer->id, 'license_plate' => 'XYZ9999']);

    Livewire::actingAs($user)
        ->test('pages::vehicles.list')
        ->set('search', 'ABC')
        ->assertSee($match->license_plate)
        ->assertDontSee($noMatch->license_plate);
});

test('search filters by brand', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    $match = Vehicle::factory()->create(['customer_id' => $customer->id, 'brand' => 'Toyota']);
    $noMatch = Vehicle::factory()->create(['customer_id' => $customer->id, 'brand' => 'Honda']);

    Livewire::actingAs($user)
        ->test('pages::vehicles.list')
        ->set('search', 'Toyota')
        ->assertSee($match->license_plate)
        ->assertDontSee($noMatch->license_plate);
});

test('search filters by customer name', function () {
    $user = User::factory()->create();

    $customerA = Customer::factory()->create(['user_id' => $user->id, 'name' => 'João Silva']);
    $customerB = Customer::factory()->create(['user_id' => $user->id, 'name' => 'Maria Souza']);

    $vehicleA = Vehicle::factory()->create(['customer_id' => $customerA->id]);
    $vehicleB = Vehicle::factory()->create(['customer_id' => $customerB->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.list')
        ->set('search', 'João')
        ->assertSee($vehicleA->license_plate)
        ->assertDontSee($vehicleB->license_plate);
});
