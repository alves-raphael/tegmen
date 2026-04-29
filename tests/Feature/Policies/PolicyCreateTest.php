<?php

use App\Enums\PolicyStatus;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Policy;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

test('policies create page is accessible to authenticated users', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('policies.create'))
        ->assertOk();
});

test('guests are redirected from policies create page', function () {
    $this->get(route('policies.create'))->assertRedirect(route('login'));
});

test('policy_number is required', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('policy_number', '')
        ->set('customer_id', (string) $customer->id)
        ->set('vehicle_id', (string) $vehicle->id)
        ->set('insurer_id', (string) $insurer->id)
        ->set('start_date', '01/01/2026')
        ->set('end_date', now()->addYear()->format('d/m/Y'))
        ->set('premium', '1500,00')
        ->call('save')
        ->assertHasErrors(['policy_number' => 'required']);
});

test('customer_id is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('customer_id', '')
        ->call('save')
        ->assertHasErrors('customer_id');
});

test('vehicle_id is required', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('customer_id', (string) $customer->id)
        ->set('vehicle_id', '')
        ->call('save')
        ->assertHasErrors('vehicle_id');
});

test('insurer_id is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('insurer_id', '')
        ->call('save')
        ->assertHasErrors('insurer_id');
});

test('end_date must be after today', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('end_date', now()->format('d/m/Y'))
        ->call('save')
        ->assertHasErrors('end_date');
});

test('end_date rejects past dates', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('end_date', now()->subDay()->format('d/m/Y'))
        ->call('save')
        ->assertHasErrors('end_date');
});

test('commission_percentage must be between 0 and 100', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('commission_percentage', '101')
        ->call('save')
        ->assertHasErrors('commission_percentage');
});

test('commission_percentage of 0 is valid', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('commission_percentage', '0')
        ->set('customer_id', (string) $customer->id)
        ->set('vehicle_id', (string) $vehicle->id)
        ->set('insurer_id', (string) $insurer->id)
        ->set('policy_number', 'POL-999')
        ->set('start_date', '01/01/2026')
        ->set('end_date', now()->addYear()->format('d/m/Y'))
        ->set('premium', '1000,00')
        ->call('save')
        ->assertHasNoErrors('commission_percentage');
});

test('commission_value is auto-calculated when percentage is set', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('premium', '1000,00')
        ->set('commission_percentage', '10')
        ->assertSet('commission_value', '100,00');
});

test('commission_value updates when premium changes', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('commission_percentage', '10')
        ->set('premium', '2000,00')
        ->assertSet('commission_value', '200,00');
});

test('commission_value is empty when percentage is empty', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('premium', '1000,00')
        ->set('commission_percentage', '')
        ->assertSet('commission_value', '');
});

test('vehicles computed property is empty when no customer is selected', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test('pages::policies.create');
    expect($component->instance()->vehicles)->toBeEmpty();
});

test('vehicles computed property is filtered by selected customer', function () {
    $user = User::factory()->create();
    $customer1 = Customer::factory()->create(['user_id' => $user->id]);
    $customer2 = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle1 = Vehicle::factory()->create(['customer_id' => $customer1->id]);
    $vehicle2 = Vehicle::factory()->create(['customer_id' => $customer2->id]);

    $component = Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('customer_id', (string) $customer1->id);

    $vehicleIds = $component->instance()->vehicles->pluck('id')->toArray();

    expect($vehicleIds)->toContain($vehicle1->id)
        ->and($vehicleIds)->not->toContain($vehicle2->id);
});

test('changing customer_id resets vehicle_id', function () {
    $user = User::factory()->create();
    $customer1 = Customer::factory()->create(['user_id' => $user->id]);
    $customer2 = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle1 = Vehicle::factory()->create(['customer_id' => $customer1->id]);

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('customer_id', (string) $customer1->id)
        ->set('vehicle_id', (string) $vehicle1->id)
        ->set('customer_id', (string) $customer2->id)
        ->assertSet('vehicle_id', '');
});

test('successful save creates policy in database', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('policy_number', 'POL-123456')
        ->set('customer_id', (string) $customer->id)
        ->set('vehicle_id', (string) $vehicle->id)
        ->set('insurer_id', (string) $insurer->id)
        ->set('start_date', '01/01/2026')
        ->set('end_date', now()->addYear()->format('d/m/Y'))
        ->set('premium', '1500,00')
        ->call('save');

    expect(Policy::where('policy_number', 'POL-123456')->exists())->toBeTrue();

    $policy = Policy::where('policy_number', 'POL-123456')->first();
    expect($policy->customer_id)->toBe($customer->id)
        ->and($policy->vehicle_id)->toBe($vehicle->id)
        ->and($policy->insurer_id)->toBe($insurer->id)
        ->and($policy->status)->toBe(PolicyStatus::Active);
});

test('successful save redirects to policies index', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('policy_number', 'POL-REDIRECT')
        ->set('customer_id', (string) $customer->id)
        ->set('vehicle_id', (string) $vehicle->id)
        ->set('insurer_id', (string) $insurer->id)
        ->set('start_date', '01/01/2026')
        ->set('end_date', now()->addYear()->format('d/m/Y'))
        ->set('premium', '1500,00')
        ->call('save')
        ->assertRedirect(route('policies.index'));
});

test('renew_from mount param pre-fills form from origin policy', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $origin = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'policy_number' => 'POL-ORIGIN',
        'premium' => 2000.00,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.create', ['renew_from' => $origin->id])
        ->assertSet('renewed_from_id', $origin->id)
        ->assertSet('customer_id', (string) $origin->customer_id)
        ->assertSet('vehicle_id', (string) $origin->vehicle_id)
        ->assertSet('insurer_id', (string) $origin->insurer_id)
        ->assertSet('policy_number', 'POL-ORIGIN');
});

test('renew_from pointing to another users policy is ignored', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $otherCustomer = Customer::factory()->create(['user_id' => $other->id]);
    $otherVehicle = Vehicle::factory()->create(['customer_id' => $otherCustomer->id]);

    $origin = Policy::factory()->active()->create([
        'customer_id' => $otherCustomer->id,
        'vehicle_id' => $otherVehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.create', ['renew_from' => $origin->id])
        ->assertSet('renewed_from_id', null);
});

test('attempting to renew a cancelled policy redirects with toast', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $origin = Policy::factory()->cancelled()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.create', ['renew_from' => $origin->id])
        ->assertRedirect(route('policies.index'));
});

test('successful renewal creates new policy and marks origin as renewed', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $origin = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'policy_number' => 'POL-ORIG',
        'premium' => 1000.00,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.create', ['renew_from' => $origin->id])
        ->set('policy_number', 'POL-RENEWED')
        ->set('premium', '1200,00')
        ->call('save');

    expect(Policy::where('policy_number', 'POL-RENEWED')->exists())->toBeTrue();

    $newPolicy = Policy::where('policy_number', 'POL-RENEWED')->first();
    expect($newPolicy->renewed_from_id)->toBe($origin->id)
        ->and($newPolicy->status)->toBe(PolicyStatus::Active);

    expect($origin->fresh()->status)->toBe(PolicyStatus::Renewed);
});

test('renewal mismatch shows danger toast and does not save', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer1 = Customer::factory()->create(['user_id' => $user->id]);
    $customer2 = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle1 = Vehicle::factory()->create(['customer_id' => $customer1->id]);
    $vehicle2 = Vehicle::factory()->create(['customer_id' => $customer2->id]);

    $origin = Policy::factory()->active()->create([
        'customer_id' => $customer1->id,
        'vehicle_id' => $vehicle1->id,
        'insurer_id' => $insurer->id,
        'policy_number' => 'POL-MISMATCH-SRC',
        'premium' => 1000.00,
    ]);

    $initialPolicyCount = Policy::count();

    // Simulate renewal but with a different customer/vehicle (mismatch)
    Livewire::actingAs($user)
        ->test('pages::policies.create', ['renew_from' => $origin->id])
        ->set('customer_id', (string) $customer2->id)
        ->set('vehicle_id', (string) $vehicle2->id)
        ->set('policy_number', 'POL-MISMATCH-NEW')
        ->set('start_date', '01/01/2026')
        ->set('end_date', now()->addYear()->format('d/m/Y'))
        ->set('insurer_id', (string) $insurer->id)
        ->set('premium', '1000,00')
        ->call('save');

    expect(Policy::count())->toBe($initialPolicyCount)
        ->and($origin->fresh()->status)->toBe(PolicyStatus::Active);
});

test('policy_number must be unique per insurer', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    Policy::factory()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'policy_number' => 'POL-DUPLICATE',
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('policy_number', 'POL-DUPLICATE')
        ->set('customer_id', (string) $customer->id)
        ->set('vehicle_id', (string) $vehicle->id)
        ->set('insurer_id', (string) $insurer->id)
        ->set('start_date', '01/01/2026')
        ->set('end_date', now()->addYear()->format('d/m/Y'))
        ->set('premium', '1500,00')
        ->call('save')
        ->assertHasErrors(['policy_number' => 'unique']);
});

test('same policy_number is allowed for a different insurer', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $otherInsurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    Policy::factory()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'policy_number' => 'POL-SHARED',
    ]);

    $initialCount = Policy::count();

    Livewire::actingAs($user)
        ->test('pages::policies.create')
        ->set('policy_number', 'POL-SHARED')
        ->set('customer_id', (string) $customer->id)
        ->set('vehicle_id', (string) $vehicle->id)
        ->set('insurer_id', (string) $otherInsurer->id)
        ->set('start_date', '01/01/2026')
        ->set('end_date', now()->addYear()->format('d/m/Y'))
        ->set('premium', '1500,00')
        ->call('save')
        ->assertHasNoErrors('policy_number');

    expect(Policy::count())->toBe($initialCount + 1);
});
