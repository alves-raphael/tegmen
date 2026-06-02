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

test('document must be in cpf format for pessoa fisica', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('type', 'person')
        ->set('name', 'João Silva')
        ->set('document', '12345678901')
        ->call('nextStep')
        ->assertHasErrors('document');
});

test('document must be in cnpj format for pessoa juridica', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('type', 'company')
        ->set('name', 'Empresa Ltda')
        ->set('document', '12345678000190')
        ->call('nextStep')
        ->assertHasErrors('document');
});

test('email must be valid', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('type', 'person')
        ->set('name', 'João Silva')
        ->set('document', '123.456.789-01')
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

test('birth date must be in dd/mm/yyyy format for pessoa fisica', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('type', 'person')
        ->set('birth_date', '1990-01-15')
        ->call('nextStep')
        ->assertHasErrors('birth_date');
});

test('birth date is not required for pessoa juridica', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('type', 'company')
        ->set('name', 'Empresa Ltda')
        ->set('document', '11.222.333/0001-81')
        ->set('email', 'empresa@example.com')
        ->set('phone', '(11) 98765-4321')
        ->call('nextStep')
        ->assertHasNoErrors('birth_date')
        ->assertSet('currentStep', 2);
});

test('nextStep does not advance when step 1 has validation errors', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->call('nextStep')
        ->assertSet('currentStep', 1);
});

test('nextStep advances to step 2 when step 1 is valid for pessoa fisica', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('type', 'person')
        ->set('name', 'João Silva')
        ->set('document', '529.982.247-25')
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
        ->set('type', 'person')
        ->set('name', 'João Silva')
        ->set('document', '529.982.247-25')
        ->set('email', 'joao@example.com')
        ->set('phone', '(11) 98765-4321')
        ->set('birth_date', '15/01/1990')
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->call('previousStep')
        ->assertSet('currentStep', 1);
});

test('save validates step 2 address fields when partial data is provided', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('type', 'person')
        ->set('name', 'João Silva')
        ->set('document', '529.982.247-25')
        ->set('email', 'joao@example.com')
        ->set('phone', '(11) 98765-4321')
        ->set('birth_date', '15/01/1990')
        ->call('nextStep')
        ->set('street', 'Rua das Flores')
        ->call('save')
        ->assertHasErrors(['zip_code', 'city', 'state', 'number', 'neighborhood']);
});

test('save without address creates customer with no address records', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('type', 'person')
        ->set('name', 'João Silva')
        ->set('document', '529.982.247-25')
        ->set('email', 'joao@example.com')
        ->set('phone', '(11) 98765-4321')
        ->set('birth_date', '15/01/1990')
        ->call('nextStep')
        ->call('save');

    $customer = Customer::where('email', 'joao@example.com')->first();
    expect($customer)->not->toBeNull();
    expect(Address::where('customer_id', $customer->id)->count())->toBe(0);
});

test('save stores document as digits only', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('type', 'person')
        ->set('name', 'João Silva')
        ->set('document', '529.982.247-25')
        ->set('email', 'joao@example.com')
        ->set('phone', '(11) 98765-4321')
        ->set('birth_date', '15/01/1990')
        ->call('nextStep')
        ->call('save');

    $customer = Customer::where('email', 'joao@example.com')->first();
    expect($customer->document)->toBe('52998224725');
    expect($customer->type->value)->toBe('person');
});

test('save company stores cnpj digits and type', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('type', 'company')
        ->set('name', 'Empresa Ltda')
        ->set('document', '11.222.333/0001-81')
        ->set('email', 'empresa@example.com')
        ->set('phone', '(11) 98765-4321')
        ->call('nextStep')
        ->call('save');

    $customer = Customer::where('email', 'empresa@example.com')->first();
    expect($customer->document)->toBe('11222333000181');
    expect($customer->type->value)->toBe('company');
    expect($customer->birth_date)->toBeNull();
});

test('successful save creates customer and address', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::customers.create')
        ->set('type', 'person')
        ->set('name', 'João Silva')
        ->set('document', '529.982.247-25')
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
        ->set('type', 'person')
        ->set('name', 'João Silva')
        ->set('document', '529.982.247-25')
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
