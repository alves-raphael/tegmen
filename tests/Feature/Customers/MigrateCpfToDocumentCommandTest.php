<?php

use App\Models\Customer;
use App\Models\User;

test('command migrates cpf to document as digits only', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'user_id' => $user->id,
        'cpf' => '529.982.247-25',
        'document' => null,
        'type' => null,
    ]);

    $this->artisan('customers:migrate-cpf-to-document')->assertSuccessful();

    $customer->refresh();
    expect($customer->document)->toBe('52998224725');
    expect($customer->type->value)->toBe('person');
});

test('command sets type to person for migrated records', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'user_id' => $user->id,
        'cpf' => '529.982.247-25',
        'document' => null,
        'type' => null,
    ]);

    $this->artisan('customers:migrate-cpf-to-document')->assertSuccessful();

    expect($customer->fresh()->type->value)->toBe('person');
});

test('command skips records that already have a document', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'user_id' => $user->id,
        'cpf' => '529.982.247-25',
        'document' => '99988877766',
        'type' => 'person',
    ]);

    $this->artisan('customers:migrate-cpf-to-document')->assertSuccessful();

    expect($customer->fresh()->document)->toBe('99988877766');
});

test('command is idempotent when run twice', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'user_id' => $user->id,
        'cpf' => '529.982.247-25',
        'document' => null,
        'type' => null,
    ]);

    $this->artisan('customers:migrate-cpf-to-document')->assertSuccessful();
    $this->artisan('customers:migrate-cpf-to-document')->assertSuccessful();

    expect($customer->fresh()->document)->toBe('52998224725');
});

test('command outputs success message', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'user_id' => $user->id,
        'cpf' => '529.982.247-25',
        'document' => null,
        'type' => null,
    ]);

    $this->artisan('customers:migrate-cpf-to-document')
        ->expectsOutputToContain('Migração concluída')
        ->assertSuccessful();
});
