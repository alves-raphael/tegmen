<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('customers:migrate-cpf-to-document')]
#[Description('Populate the document and type columns from the legacy cpf column')]
class MigrateCpfToDocument extends Command
{
    public function handle(): int
    {
        $total = Customer::whereNull('document')->whereNotNull('cpf')->count();

        if ($total === 0) {
            $this->info('Nenhum cliente para migrar.');

            return self::SUCCESS;
        }

        $this->info("Migrando {$total} cliente(s)...");

        $migrated = 0;

        Customer::whereNull('document')
            ->whereNotNull('cpf')
            ->chunkById(200, function ($customers) use (&$migrated) {
                foreach ($customers as $customer) {
                    $customer->updateQuietly([
                        'document' => preg_replace('/\D/', '', $customer->cpf),
                        'type' => 'person',
                    ]);
                    $migrated++;
                }
            });

        $this->info("Migração concluída: {$migrated} cliente(s) atualizados.");

        return self::SUCCESS;
    }
}
