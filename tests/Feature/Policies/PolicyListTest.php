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

test('insurer filter shows only policies from the selected insurer', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $otherInsurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $myPolicy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    $otherPolicy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $otherInsurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->set('statusFilter', '')
        ->set('insurerFilter', $insurer->id)
        ->assertSee($myPolicy->policy_number)
        ->assertDontSee($otherPolicy->policy_number);
});

test('insurer filter dropdown only lists insurers the user has policies with', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $myInsurer = InsuranceCompany::factory()->create();
    $otherInsurer = InsuranceCompany::factory()->create();

    $myCustomer = Customer::factory()->create(['user_id' => $user->id]);
    $myVehicle = Vehicle::factory()->create(['customer_id' => $myCustomer->id]);
    Policy::factory()->active()->create([
        'customer_id' => $myCustomer->id,
        'vehicle_id' => $myVehicle->id,
        'insurer_id' => $myInsurer->id,
    ]);

    $otherCustomer = Customer::factory()->create(['user_id' => $otherUser->id]);
    $otherVehicle = Vehicle::factory()->create(['customer_id' => $otherCustomer->id]);
    Policy::factory()->active()->create([
        'customer_id' => $otherCustomer->id,
        'vehicle_id' => $otherVehicle->id,
        'insurer_id' => $otherInsurer->id,
    ]);

    $component = Livewire::actingAs($user)->test('pages::policies.index');
    $insurerIds = $component->instance()->insurers->pluck('id');

    expect($insurerIds)->toContain($myInsurer->id)
        ->and($insurerIds)->not->toContain($otherInsurer->id);
});

test('clearing insurer filter shows all policies again', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $otherInsurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $policy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
    ]);

    $otherPolicy = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $otherInsurer->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->set('insurerFilter', $insurer->id)
        ->assertSee($policy->policy_number)
        ->assertDontSee($otherPolicy->policy_number)
        ->set('insurerFilter', '')
        ->assertSee($policy->policy_number)
        ->assertSee($otherPolicy->policy_number);
});

test('end date from filter shows only policies expiring on or after date', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $before = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'end_date' => now()->addDays(10)->toDateString(),
    ]);

    $after = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'end_date' => now()->addDays(60)->toDateString(),
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->set('endDateFrom', now()->addDays(30)->format('d/m/Y'))
        ->assertDontSee($before->policy_number)
        ->assertSee($after->policy_number);
});

test('end date to filter shows only policies expiring on or before date', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $before = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'end_date' => now()->addDays(10)->toDateString(),
    ]);

    $after = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'end_date' => now()->addDays(60)->toDateString(),
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->set('endDateTo', now()->addDays(30)->format('d/m/Y'))
        ->assertSee($before->policy_number)
        ->assertDontSee($after->policy_number);
});

test('end date range filters policies between dates', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    $tooEarly = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'end_date' => now()->addDays(5)->toDateString(),
    ]);

    $inRange = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'end_date' => now()->addDays(20)->toDateString(),
    ]);

    $tooLate = Policy::factory()->active()->create([
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'insurer_id' => $insurer->id,
        'end_date' => now()->addDays(60)->toDateString(),
    ]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->set('endDateFrom', now()->addDays(10)->format('d/m/Y'))
        ->set('endDateTo', now()->addDays(30)->format('d/m/Y'))
        ->assertDontSee($tooEarly->policy_number)
        ->assertSee($inRange->policy_number)
        ->assertDontSee($tooLate->policy_number);
});

test('clear filters resets all filters to defaults', function () {
    $user = User::factory()->create();
    $insurer = InsuranceCompany::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

    Livewire::actingAs($user)
        ->test('pages::policies.index')
        ->set('statusFilter', 'CANCELLED')
        ->set('insurerFilter', $insurer->id)
        ->set('endDateFrom', now()->addDays(10)->format('d/m/Y'))
        ->set('endDateTo', now()->addDays(30)->format('d/m/Y'))
        ->call('clearFilters')
        ->assertSet('statusFilter', 'ACTIVE')
        ->assertSet('insurerFilter', '')
        ->assertSet('endDateFrom', '')
        ->assertSet('endDateTo', '');
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
