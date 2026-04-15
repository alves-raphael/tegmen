<?php

use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

test('changing address deactivates old and creates new active record', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    Address::factory()->create(['customer_id' => $customer->id, 'street' => 'Rua Antiga', 'status' => true]);

    Livewire::actingAs($user)
        ->test('pages::customers.edit', ['customer' => $customer])
        ->set('name', $customer->name)
        ->set('cpf', $customer->cpf)
        ->set('email', $customer->email)
        ->set('phone', $customer->phone)
        ->set('birth_date', $customer->birth_date->format('d/m/Y'))
        ->call('nextStep')
        ->set('street', 'Rua Nova')
        ->set('zip_code', '01310-100')
        ->set('neighborhood', 'Centro')
        ->set('state', 'SP')
        ->set('city', 'São Paulo')
        ->set('number', '200')
        ->call('save');

    expect(Address::where('customer_id', $customer->id)->where('status', false)->count())->toBe(1);
    expect(Address::where('customer_id', $customer->id)->where('status', true)->count())->toBe(1);
    expect(Address::where('customer_id', $customer->id)->where('status', true)->first()->street)->toBe('Rua Nova');
});

test('unchanged address does not create a new record', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    Address::factory()->create([
        'customer_id' => $customer->id,
        'street' => 'Rua Sem Mudança',
        'zip_code' => '01310-100',
        'neighborhood' => 'Centro',
        'state' => 'SP',
        'city' => 'São Paulo',
        'number' => '100',
        'complement' => null,
        'status' => true,
    ]);

    Livewire::actingAs($user)
        ->test('pages::customers.edit', ['customer' => $customer])
        ->set('name', $customer->name)
        ->set('cpf', $customer->cpf)
        ->set('email', $customer->email)
        ->set('phone', $customer->phone)
        ->set('birth_date', $customer->birth_date->format('d/m/Y'))
        ->call('nextStep')
        ->call('save');

    expect(Address::where('customer_id', $customer->id)->count())->toBe(1);
    expect(Address::where('customer_id', $customer->id)->where('status', true)->count())->toBe(1);
});

test('customer always has at most one active address', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    Address::factory()->create(['customer_id' => $customer->id, 'street' => 'Rua 1', 'status' => true]);

    // First address change
    Livewire::actingAs($user)
        ->test('pages::customers.edit', ['customer' => $customer])
        ->set('name', $customer->name)
        ->set('cpf', $customer->cpf)
        ->set('email', $customer->email)
        ->set('phone', $customer->phone)
        ->set('birth_date', $customer->birth_date->format('d/m/Y'))
        ->call('nextStep')
        ->set('street', 'Rua 2')
        ->set('zip_code', '01310-100')
        ->set('neighborhood', 'Centro')
        ->set('state', 'SP')
        ->set('city', 'São Paulo')
        ->set('number', '200')
        ->call('save');

    expect(Address::where('customer_id', $customer->id)->where('status', true)->count())->toBe(1);
});
