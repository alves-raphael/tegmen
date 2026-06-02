<?php

use App\Enums\CustomerType;
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
        ->assertSet('type', CustomerType::Person->value);
});

test('edit form pre-populates document formatted for cpf', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'user_id' => $user->id,
        'type' => CustomerType::Person,
        'document' => '52998224725',
    ]);

    Livewire::actingAs($user)
        ->test('pages::customers.edit', ['customer' => $customer])
        ->assertSet('document', '529.982.247-25');
});

test('edit form pre-populates document formatted for cnpj', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->company()->create(['user_id' => $user->id]);

    $formatted = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $customer->document);

    Livewire::actingAs($user)
        ->test('pages::customers.edit', ['customer' => $customer])
        ->assertSet('document', $formatted);
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

test('save without address data leaves existing address unchanged', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $address = Address::factory()->create(['customer_id' => $customer->id, 'status' => true]);

    Livewire::actingAs($user)
        ->test('pages::customers.edit', ['customer' => $customer])
        ->set('street', '')
        ->set('zip_code', '')
        ->set('neighborhood', '')
        ->set('city', '')
        ->set('state', '')
        ->set('number', '')
        ->call('nextStep')
        ->call('save');

    expect($address->fresh()->status)->toBeTrue();
    expect(Address::where('customer_id', $customer->id)->where('status', true)->count())->toBe(1);
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

test('save stores document as digits only on update', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::customers.edit', ['customer' => $customer])
        ->set('document', '529.982.247-25')
        ->call('nextStep')
        ->call('save');

    expect($customer->fresh()->document)->toBe('52998224725');
});
