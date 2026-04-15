<?php

use App\Models\InsuranceCompany;
use Database\Seeders\InsuranceCompanySeeder;

test('seeder creates insurance companies', function () {
    $this->seed(InsuranceCompanySeeder::class);

    expect(InsuranceCompany::count())->toBeGreaterThanOrEqual(20);
});

test('seeder is idempotent', function () {
    $this->seed(InsuranceCompanySeeder::class);
    $countAfterFirst = InsuranceCompany::count();

    $this->seed(InsuranceCompanySeeder::class);

    expect(InsuranceCompany::count())->toBe($countAfterFirst);
});

test('all seeded companies have status true', function () {
    $this->seed(InsuranceCompanySeeder::class);

    expect(InsuranceCompany::where('status', false)->count())->toBe(0);
});
