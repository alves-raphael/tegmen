<?php

namespace Database\Seeders;

use App\Models\InsuranceCompany;
use Illuminate\Database\Seeder;

class InsuranceCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            'Allianz Seguros',
            'Azul Seguros',
            'BB Seguros',
            'Bradesco Seguros',
            'Caixa Seguradora',
            'Chubb Seguros Brasil',
            'Fairfax Brasil Seguros',
            'Generali Brasil Seguros',
            'HDI Seguros',
            'Itaú Seguros',
            'Liberty Seguros',
            'Mapfre Seguros',
            'Mitsui Sumitomo Seguros',
            'Omint Seguros',
            'Porto Seguro',
            'Sancor Seguros do Brasil',
            'SulAmérica Seguros',
            'Tokio Marine Seguradora',
            'Travelers Seguros',
            'Unimed Seguros',
            'Wiz Seguros',
            'Zurich Seguros',
        ];

        foreach ($companies as $name) {
            InsuranceCompany::firstOrCreate(
                ['name' => $name],
                ['status' => true]
            );
        }
    }
}
