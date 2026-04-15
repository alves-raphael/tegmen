<?php

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;

test('editing vehicle for another users customer returns 403', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $otherUser->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $this->actingAs($user)
        ->get(route('vehicles.edit', [$customer, $vehicle]))
        ->assertForbidden();
});

test('unauthorized vehicle edit attempt is logged with attempted value', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $otherUser->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    Log::shouldReceive('warning')
        ->once()
        ->with('Unauthorized vehicle edit attempt', Mockery::on(function ($context) use ($otherUser) {
            return isset($context['attempted_customer_user_id'])
                && $context['attempted_customer_user_id'] === $otherUser->id;
        }));

    Livewire::actingAs($user)
        ->test('pages::vehicles.edit', ['customer' => $customer, 'vehicle' => $vehicle]);
});

test('vehicle not belonging to given customer returns 404', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $otherCustomer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $otherCustomer->id]);

    $this->actingAs($user)
        ->get(route('vehicles.edit', [$customer, $vehicle]))
        ->assertNotFound();
});

test('edit form is pre-populated with vehicle data', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.edit', ['customer' => $customer, 'vehicle' => $vehicle])
        ->assertSet('license_plate', $vehicle->license_plate)
        ->assertSet('brand', $vehicle->brand)
        ->assertSet('model', $vehicle->model)
        ->assertSet('model_year', $vehicle->model_year)
        ->assertSet('usage', $vehicle->usage)
        ->assertSet('color', $vehicle->color);
});

test('successful edit updates vehicle fields', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.edit', ['customer' => $customer, 'vehicle' => $vehicle])
        ->set('license_plate', 'XYZ-9999')
        ->set('brand', 'Fiat')
        ->set('model', 'Palio')
        ->set('model_year', '2022')
        ->set('usage', 'work')
        ->set('color', 'red')
        ->call('save');

    expect($vehicle->fresh()->license_plate)->toBe('XYZ-9999');
    expect($vehicle->fresh()->brand)->toBe('Fiat');
});
