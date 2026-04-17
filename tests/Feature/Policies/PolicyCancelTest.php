<?php

use App\Enums\PolicyStatus;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Policy;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

function makePolicySetup(User $user, InsuranceCompany $insurer): array
{
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    return compact('customer', 'vehicle');
}

test('openCancelModal sets correct state', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    ['customer' => $customer, 'vehicle' => $vehicle] = makePolicySetup($user, $insurer);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->call('openCancelModal', $policy->id)
        ->assertSet('showCancelModal', true)
        ->assertSet('cancellingPolicyId', $policy->id);
});

test('closeCancelModal resets state', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    ['customer' => $customer, 'vehicle' => $vehicle] = makePolicySetup($user, $insurer);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->call('openCancelModal', $policy->id)
        ->call('closeCancelModal')
        ->assertSet('showCancelModal', false)
        ->assertSet('cancellingPolicyId', null);
});

test('active policy can be cancelled', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    ['customer' => $customer, 'vehicle' => $vehicle] = makePolicySetup($user, $insurer);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->call('openCancelModal', $policy->id)
        ->call('cancelPolicy');

    expect($policy->fresh()->status)->toBe(PolicyStatus::Cancelled)
        ->and($policy->fresh()->cancelled_at)->not->toBeNull();
});

test('cancelled_at is set when policy is cancelled', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    ['customer' => $customer, 'vehicle' => $vehicle] = makePolicySetup($user, $insurer);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->call('openCancelModal', $policy->id)
        ->call('cancelPolicy');

    expect($policy->fresh()->cancelled_at)->not->toBeNull();
});

test('cancelling an already-cancelled policy shows danger toast without changing status', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    ['customer' => $customer, 'vehicle' => $vehicle] = makePolicySetup($user, $insurer);

    $policy = Policy::factory()->cancelled()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    $originalCancelledAt = $policy->cancelled_at->toDateTimeString();

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->call('openCancelModal', $policy->id)
        ->call('cancelPolicy');

    expect($policy->fresh()->status)->toBe(PolicyStatus::Cancelled)
        ->and($policy->fresh()->cancelled_at->toDateTimeString())->toBe($originalCancelledAt);
});

test('cancelling a renewed policy is blocked', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    ['customer' => $customer, 'vehicle' => $vehicle] = makePolicySetup($user, $insurer);

    $policy = Policy::factory()->renewed()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->call('openCancelModal', $policy->id)
        ->call('cancelPolicy');

    expect($policy->fresh()->status)->toBe(PolicyStatus::Renewed);
});

test('cancelling an expired policy is blocked', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    ['customer' => $customer, 'vehicle' => $vehicle] = makePolicySetup($user, $insurer);

    $policy = Policy::factory()->expired()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->call('openCancelModal', $policy->id)
        ->call('cancelPolicy');

    expect($policy->fresh()->status)->toBe(PolicyStatus::Expired);
});

test('cancelling another user policy is blocked', function () {
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

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->call('openCancelModal', $policy->id)
        ->call('cancelPolicy');

    expect($policy->fresh()->status)->toBe(PolicyStatus::Active);
});

test('successful cancellation closes the modal', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    ['customer' => $customer, 'vehicle' => $vehicle] = makePolicySetup($user, $insurer);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->call('openCancelModal', $policy->id)
        ->call('cancelPolicy')
        ->assertSet('showCancelModal', false)
        ->assertSet('cancellingPolicyId', null);
});
