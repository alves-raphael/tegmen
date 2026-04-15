<?php

use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;

test('guests are redirected from customer edit page', function () {
    $customer = Customer::factory()->create();

    $this->get(route('customers.edit', $customer))->assertRedirect(route('login'));
});

test('edit page is accessible to the customer owner', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('customers.edit', $customer))
        ->assertOk();
});

test('edit form is pre-populated with customer data', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::customers.edit', ['customer' => $customer])
        ->assertSet('name', $customer->name)
        ->assertSet('email', $customer->email)
        ->assertSet('cpf', $customer->cpf);
});

test('edit form is pre-populated with active address', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    Address::factory()->create(['customer_id' => $customer->id, 'street' => 'Rua Teste', 'status' => true]);

    Livewire::actingAs($user)
        ->test('pages::customers.edit', ['customer' => $customer])
        ->assertSet('street', 'Rua Teste');
});

test('unauthorized access returns 403', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->get(route('customers.edit', $customer))
        ->assertForbidden();
});

test('unauthorized access attempt is logged', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $otherUser->id]);

    Log::shouldReceive('warning')
        ->once()
        ->with('Unauthorized customer edit attempt', Mockery::type('array'));

    Livewire::actingAs($user)
        ->test('pages::customers.edit', ['customer' => $customer]);
});

test('successful update changes customer fields', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    Address::factory()->create(['customer_id' => $customer->id, 'status' => true]);

    Livewire::actingAs($user)
        ->test('pages::customers.edit', ['customer' => $customer])
        ->set('name', 'Nome Atualizado')
        ->call('nextStep')
        ->call('save');

    expect($customer->fresh()->name)->toBe('Nome Atualizado');
});
