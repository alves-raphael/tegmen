<?php

use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

test('customer create page is accessible to authenticated users', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('customers.create'))
        ->assertOk();
});

test('guests are redirected from customer create page', function () {
    $this->get(route('customers.create'))->assertRedirect(route('login'));
});

test('name requires minimum 2 characters', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('name', 'A')
        ->call('nextStep')
        ->assertHasErrors(['name' => 'min']);
});

test('name requires at least 2 characters to advance', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('name', 'Jo')
        ->call('nextStep')
        ->assertHasNoErrors('name');
});

test('cpf must be in correct format', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('name', 'João Silva')
        ->set('cpf', '12345678901')
        ->call('nextStep')
        ->assertHasErrors('cpf');
});

test('email must be valid', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('name', 'João Silva')
        ->set('cpf', '123.456.789-01')
        ->set('email', 'not-an-email')
        ->call('nextStep')
        ->assertHasErrors('email');
});

test('phone must match mask format', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('phone', '11999999999')
        ->call('nextStep')
        ->assertHasErrors('phone');
});

test('birth date must be in dd/mm/yyyy format', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('birth_date', '1990-01-15')
        ->call('nextStep')
        ->assertHasErrors('birth_date');
});

test('nextStep does not advance when step 1 has validation errors', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->call('nextStep')
        ->assertSet('currentStep', 1);
});

test('nextStep advances to step 2 when step 1 is valid', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('name', 'João Silva')
        ->set('cpf', '529.982.247-25')
        ->set('email', 'joao@example.com')
        ->set('phone', '(11) 98765-4321')
        ->set('birth_date', '15/01/1990')
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->assertHasNoErrors();
});

test('previousStep returns to step 1', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('name', 'João Silva')
        ->set('cpf', '529.982.247-25')
        ->set('email', 'joao@example.com')
        ->set('phone', '(11) 98765-4321')
        ->set('birth_date', '15/01/1990')
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->call('previousStep')
        ->assertSet('currentStep', 1);
});

test('save validates step 2 address fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('name', 'João Silva')
        ->set('cpf', '529.982.247-25')
        ->set('email', 'joao@example.com')
        ->set('phone', '(11) 98765-4321')
        ->set('birth_date', '15/01/1990')
        ->call('nextStep')
        ->call('save')
        ->assertHasErrors(['street', 'zip_code', 'city', 'state', 'number', 'neighborhood']);
});

test('successful save creates customer and address', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('name', 'João Silva')
        ->set('cpf', '529.982.247-25')
        ->set('email', 'joao@example.com')
        ->set('phone', '(11) 98765-4321')
        ->set('birth_date', '15/01/1990')
        ->set('street', 'Rua das Flores')
        ->set('zip_code', '01310-100')
        ->set('neighborhood', 'Centro')
        ->set('state', 'SP')
        ->set('city', 'São Paulo')
        ->set('number', '100')
        ->call('save');

    expect(Customer::where('email', 'joao@example.com')->exists())->toBeTrue();

    $customer = Customer::where('email', 'joao@example.com')->first();
    expect($customer->user_id)->toBe($user->id);
    expect(Address::where('customer_id', $customer->id)->where('status', true)->exists())->toBeTrue();
});

test('successful save redirects to customer index', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('name', 'João Silva')
        ->set('cpf', '529.982.247-25')
        ->set('email', 'joao@example.com')
        ->set('phone', '(11) 98765-4321')
        ->set('birth_date', '15/01/1990')
        ->set('street', 'Rua das Flores')
        ->set('zip_code', '01310-100')
        ->set('neighborhood', 'Centro')
        ->set('state', 'SP')
        ->set('city', 'São Paulo')
        ->set('number', '100')
        ->call('save')
        ->assertRedirect(route('customers.index'));
});
