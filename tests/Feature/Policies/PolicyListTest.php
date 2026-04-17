<?php

use App\Enums\PolicyStatus;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Policy;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

test('policies index page is accessible to authenticated users', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('policies.index'))
        ->assertOk();
});

test('guests are redirected from policies index page', function () {
    $this->get(route('policies.index'))->assertRedirect(route('login'));
});

test('policies are scoped to the authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $insurer = InsuranceCompany::factory()->create();

    $myCustomer = Customer::factory()->create(['user_id' => $user->id]);
    $myVehicle = Vehicle::factory()->create(['customer_id' => $myCustomer->id]);
    $myPolicy = Policy::factory()->create([
        'customer_id' => $myCustomer->id,
        'vehicle_id' => $myVehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    $otherCustomer = Customer::factory()->create(['user_id' => $other->id]);
    $otherVehicle = Vehicle::factory()->create(['customer_id' => $otherCustomer->id]);
    $otherPolicy = Policy::factory()->create([
        'customer_id' => $otherCustomer->id,
        'vehicle_id' => $otherVehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->assertSee($myCustomer->name)
        ->assertDontSee($otherCustomer->name);
});

test('default status filter shows only active policies', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $active = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    $cancelled = Policy::factory()->cancelled()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->assertSet('statusFilter', 'ACTIVE')
        ->assertSee($active->policy_number)
        ->assertDontSee($cancelled->policy_number);
});

test('status filter shows policies matching selected status', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $active = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    $cancelled = Policy::factory()->cancelled()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->set('statusFilter', 'CANCELLED')
        ->assertSee($cancelled->policy_number)
        ->assertDontSee($active->policy_number);
});

test('policies are sorted by end_date ascending', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $later = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'end_date' => now()->addDays(60)->toDateString(),
    ]);

    $sooner = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'end_date' => now()->addDays(35)->toDateString(),
    ]);

    $component = Livewire::actingAs($user)->test('pages::policies.index');
    $ids = $component->instance()->policies->pluck('id')->toArray();

    expect($ids[0])->toBe($sooner->id)
        ->and($ids[1])->toBe($later->id);
});

test('cancel button is only visible for active policies', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $active = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    $cancelled = Policy::factory()->cancelled()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    // Check via enum helpers
    expect($active->status->isCancellable())->toBeTrue()
        ->and($cancelled->status->isCancellable())->toBeFalse();
});

test('renewal link is only available for active policies', function () {
    $user = User::factory()->create();

    $active = Policy::factory()->active()->make();
    $cancelled = Policy::factory()->cancelled()->make();
    $renewed = Policy::factory()->renewed()->make();
    $expired = Policy::factory()->expired()->make();

    expect($active->status->isRenewable())->toBeTrue()
        ->and($cancelled->status->isRenewable())->toBeFalse()
        ->and($renewed->status->isRenewable())->toBeFalse()
        ->and($expired->status->isRenewable())->toBeFalse();
});

test('status badges have correct colors', function () {
    expect(PolicyStatus::Active->badgeColor())->toBe('green')
        ->and(PolicyStatus::Renewed->badgeColor())->toBe('sky')
        ->and(PolicyStatus::Cancelled->badgeColor())->toBe('red')
        ->and(PolicyStatus::Expired->badgeColor())->toBe('red');
});

test('all status filter shows all policies regardless of status', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $active = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    $cancelled = Policy::factory()->cancelled()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->set('statusFilter', '')
        ->assertSee($active->policy_number)
        ->assertSee($cancelled->policy_number);
});
