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

test('double year format is accepted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.create', ['customer' => $customer])
        ->set('license_plate', 'ABC-1234')
        ->set('brand', 'VW')
        ->set('model', 'Gol')
        ->set('model_year', '2020/2021')
        ->set('usage', 'personal')
        ->set('color', 'white')
        ->call('save')
        ->assertHasNoErrors('model_year');

    expect(Vehicle::where('customer_id', $customer->id)->value('model_year'))->toBe('2020/2021');
});

test('model_year rejects invalid formats', function (string $year) {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.create', ['customer' => $customer])
        ->set('model_year', $year)
        ->call('save')
        ->assertHasErrors('model_year');
})->with(['20201', '2020/20221', 'abcd', '20ab/2021', '']);

test('double year with second year less than first is rejected', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.create', ['customer' => $customer])
        ->set('model_year', '2021/2020')
        ->call('save')
        ->assertHasErrors('model_year');
});

test('model_year boundary years are accepted', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::vehicles.create', ['customer' => $customer])
        ->set('license_plate', 'ABC-1234')
        ->set('brand', 'VW')
        ->set('model', 'Gol')
        ->set('model_year', '1900')
        ->set('usage', 'personal')
        ->set('color', 'white')
        ->call('save')
        ->assertHasNoErrors('model_year');
});
