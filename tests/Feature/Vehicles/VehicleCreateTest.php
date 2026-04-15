<?php

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

test('vehicle create page is accessible to the customer owner', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('vehicles.create', $customer))
        ->assertOk();
});

test('creating vehicle for another users customer returns 403', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->get(route('vehicles.create', $customer))
        ->assertForbidden();
});

test('license plate validates old format', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.create', ['customer' => $customer])
        ->set('license_plate', 'ABC-1234')
        ->set('brand', 'VW')
        ->set('model', 'Gol')
        ->set('model_year', '2020')
        ->set('usage', 'personal')
        ->set('color', 'white')
        ->call('save')
        ->assertHasNoErrors('license_plate');
});

test('license plate validates mercosul format', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.create', ['customer' => $customer])
        ->set('license_plate', 'ABC1D23')
        ->set('brand', 'VW')
        ->set('model', 'Gol')
        ->set('model_year', '2020')
        ->set('usage', 'personal')
        ->set('color', 'white')
        ->call('save')
        ->assertHasNoErrors('license_plate');
});

test('license plate rejects invalid format', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.create', ['customer' => $customer])
        ->set('license_plate', '12345678')
        ->call('save')
        ->assertHasErrors('license_plate');
});

test('save validates all required fields', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.create', ['customer' => $customer])
        ->call('save')
        // usage and color have default values so they won't fail required validation
        ->assertHasErrors(['license_plate', 'brand', 'model', 'model_year']);
});

test('fipe is optional and save succeeds without it', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.create', ['customer' => $customer])
        ->set('license_plate', 'ABC-1234')
        ->set('brand', 'VW')
        ->set('model', 'Gol')
        ->set('model_year', '2020')
        ->set('usage', 'personal')
        ->set('color', 'white')
        ->call('save')
        ->assertHasNoErrors();

    expect(Vehicle::where('customer_id', $customer->id)->exists())->toBeTrue();
});

test('usage must be one of the predefined values', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.create', ['customer' => $customer])
        ->set('usage', 'invalid-usage')
        ->call('save')
        ->assertHasErrors('usage');
});

test('successful save creates vehicle with correct customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.create', ['customer' => $customer])
        ->set('license_plate', 'ABC-1234')
        ->set('brand', 'Volkswagen')
        ->set('model', 'Gol')
        ->set('model_year', '2020')
        ->set('usage', 'personal')
        ->set('color', 'white')
        ->call('save');

    $vehicle = Vehicle::where('customer_id', $customer->id)->first();
    expect($vehicle)->not->toBeNull();
    expect($vehicle->license_plate)->toBe('ABC-1234');
});
