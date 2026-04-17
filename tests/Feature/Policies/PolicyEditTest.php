<?php

use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Policy;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

test('policies edit page is accessible to authenticated users', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    $this->actingAs($user)
        ->get(route('policies.edit', $policy))
        ->assertOk();
});

test('guests are redirected from policies edit page', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    $this->get(route('policies.edit', $policy))->assertRedirect(route('login'));
});

test('editing another users policy returns 403', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $otherCustomer = Customer::factory()->create(['user_id' => $other->id]);
    $otherVehicle = Vehicle::factory()->create(['customer_id' => $otherCustomer->id]);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $otherCustomer->id,
        'vehicle_id' => $otherVehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    $this->actingAs($user)
        ->get(route('policies.edit', $policy))
        ->assertForbidden();
});

test('edit page pre-fills all fields from the policy', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'policy_number' => 'POL-EDIT-TEST',
        'commission_percentage' => 10.00,
        'commission_value' => 150.00,
        'notes' => 'Test note',
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.edit', ['policy' => $policy])
        ->assertSet('policy_number', 'POL-EDIT-TEST')
        ->assertSet('customer_id', (string) $customer->id)
        ->assertSet('vehicle_id', (string) $vehicle->id)
        ->assertSet('insurer_id', (string) $insurer->id)
        ->assertSet('notes', 'Test note');
});

test('save updates only commission and notes fields', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'policy_number' => 'POL-ORIGINAL',
        'premium' => 1000.00,
        'commission_percentage' => null,
        'commission_value' => null,
        'notes' => null,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.edit', ['policy' => $policy])
        ->set('commission_percentage', '10')
        ->set('notes', 'Nova observação')
        ->call('save');

    $updated = $policy->fresh();
    expect($updated->commission_percentage)->toBe('10.00')
        ->and($updated->notes)->toBe('Nova observação')
        ->and($updated->policy_number)->toBe('POL-ORIGINAL');
});

test('commission_value is auto-calculated when percentage changes on edit', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'premium' => 2000.00,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.edit', ['policy' => $policy])
        ->set('commission_percentage', '5')
        ->assertSet('commission_value', '100,00');
});

test('commission_percentage must be between 0 and 100 on edit', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.edit', ['policy' => $policy])
        ->set('commission_percentage', '101')
        ->call('save')
        ->assertHasErrors('commission_percentage');
});

test('notes max length is enforced on edit', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.edit', ['policy' => $policy])
        ->set('notes', str_repeat('x', 2001))
        ->call('save')
        ->assertHasErrors('notes');
});

test('successful edit redirects to policies index', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.edit', ['policy' => $policy])
        ->call('save')
        ->assertRedirect(route('policies.index'));
});

test('edit does not expose policy_number field for modification', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'policy_number' => 'POL-IMMUTABLE',
    ]);

    // Even if policy_number is changed via Livewire (e.g. devtools), save() must ignore it
    Livewire::actingAs($user)
        ->test('pages::policies.edit', ['policy' => $policy])
        ->set('policy_number', 'HACKED')
        ->call('save');

    expect($policy->fresh()->policy_number)->toBe('POL-IMMUTABLE');
});
