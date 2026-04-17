<?php

use App\Concerns\PolicyValidationRules;
use App\Enums\PolicyStatus;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Policy;
use App\Models\Vehicle;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Nova Apólice')] class extends Component {
    use PolicyValidationRules;

    public ?int $renewed_from_id = null;

    public string $policy_number = '';
    public string $customer_id = '';
    public string $vehicle_id = '';
    public string $insurer_id = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $premium = '';
    public string $commission_percentage = '';
    public string $commission_value = '';
    public string $notes = '';

    public function mount(?int $renew_from = null): void
    {
        // Accept from mount prop (tests) or query string (browser via wire:navigate)
        $renewFromId = $renew_from ?? ((int) request()->query('renew_from') ?: null);

        if (! $renewFromId) {
            return;
        }

        $origin = Policy::with(['customer', 'vehicle', 'insurer'])
            ->find($renewFromId);

        if (! $origin || ! $origin->belongsToUser(Auth::id())) {
            return;
        }

        if (! $origin->status->isRenewable()) {
            Flux::toast(variant: 'danger', text: __('Esta apólice não pode ser renovada.'));
            $this->redirectRoute('policies.index', navigate: true);

            return;
        }

        $this->renewed_from_id = $origin->id;
        $this->customer_id = (string) $origin->customer_id;
        $this->vehicle_id = (string) $origin->vehicle_id;
        $this->insurer_id = (string) $origin->insurer_id;
        $this->policy_number = $origin->policy_number;
        $this->start_date = now()->format('d/m/Y');
        $this->end_date = $origin->end_date->addYear()->format('d/m/Y');
        $this->premium = number_format($origin->premium, 2, ',', '.');
        $this->commission_percentage = $origin->commission_percentage
            ? rtrim(rtrim(number_format($origin->commission_percentage, 2, '.', ''), '0'), '.')
            : '';
        $this->commission_value = $origin->commission_value
            ? number_format($origin->commission_value, 2, ',', '.')
            : '';
    }

    /**
     * @return Collection<int, Customer>
     */
    #[Computed]
    public function customers(): Collection
    {
        return Customer::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, InsuranceCompany>
     */
    #[Computed]
    public function insurers(): Collection
    {
        return InsuranceCompany::where('status', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Vehicle>
     */
    #[Computed]
    public function vehicles(): Collection
    {
        if (! $this->customer_id) {
            return new Collection;
        }

        return Vehicle::where('customer_id', (int) $this->customer_id)
            ->orderBy('brand')
            ->get();
    }

    public function updatedCustomerId(): void
    {
        $this->vehicle_id = '';
        unset($this->vehicles);
    }

    public function updatedCommissionPercentage(): void
    {
        $this->recalculateCommission();
    }

    public function updatedPremium(): void
    {
        $this->recalculateCommission();
    }

    private function recalculateCommission(): void
    {
        $premium = $this->parseCurrencyToFloat($this->premium);
        $percentage = (float) str_replace(',', '.', $this->commission_percentage);

        if ($premium > 0 && $percentage > 0 && $percentage <= 100) {
            $value = round($premium * $percentage / 100, 2);
            $this->commission_value = number_format($value, 2, ',', '.');
        } else {
            $this->commission_value = '';
        }
    }

    private function parseCurrencyToFloat(string $value): float
    {
        $clean = preg_replace('/[^\d,]/', '', $value);
        $clean = str_replace(',', '.', $clean ?? '');

        return (float) $clean;
    }

    public function save(): void
    {
        // Strip currency mask before validation
        $this->premium = (string) $this->parseCurrencyToFloat($this->premium);
        $this->commission_value = $this->commission_value
            ? (string) $this->parseCurrencyToFloat($this->commission_value)
            : '';

        $this->validate($this->policyRules());

        // Verify vehicle belongs to selected customer
        $vehicle = Vehicle::find((int) $this->vehicle_id);
        if (! $vehicle || (int) $vehicle->customer_id !== (int) $this->customer_id) {
            Flux::toast(variant: 'danger', text: __('Veículo não pertence ao cliente selecionado.'));

            return;
        }

        // Renewal-specific validations
        if ($this->renewed_from_id) {
            $origin = Policy::with('customer')->find($this->renewed_from_id);

            if (
                ! $origin
                || (int) $origin->customer_id !== (int) $this->customer_id
                || (int) $origin->vehicle_id !== (int) $this->vehicle_id
            ) {
                Log::warning('Policy renewal mismatch', [
                    'user_id' => Auth::id(),
                    'renewed_from_id' => $this->renewed_from_id,
                    'submitted_customer_id' => $this->customer_id,
                    'submitted_vehicle_id' => $this->vehicle_id,
                ]);
                Flux::toast(variant: 'danger', text: __('Erro ao processar renovação. Tente novamente.'));

                return;
            }

            if (! $origin->status->isRenewable()) {
                Flux::toast(variant: 'danger', text: __('Esta apólice não pode ser renovada.'));

                return;
            }
        }

        try {
            DB::transaction(function () {
                Policy::create([
                    'customer_id' => (int) $this->customer_id,
                    'vehicle_id' => (int) $this->vehicle_id,
                    'insurer_id' => (int) $this->insurer_id,
                    'policy_number' => $this->policy_number,
                    'start_date' => Carbon::createFromFormat('d/m/Y', $this->start_date)->toDateString(),
                    'end_date' => Carbon::createFromFormat('d/m/Y', $this->end_date)->toDateString(),
                    'premium' => (float) $this->premium,
                    'commission_percentage' => $this->commission_percentage !== '' ? (float) $this->commission_percentage : null,
                    'commission_value' => $this->commission_value !== '' ? (float) $this->commission_value : null,
                    'renewed_from_id' => $this->renewed_from_id,
                    'notes' => $this->notes ?: null,
                ]);

                if ($this->renewed_from_id) {
                    Policy::where('id', $this->renewed_from_id)
                        ->update(['status' => PolicyStatus::Renewed]);
                }
            });

            Flux::toast(variant: 'success', text: __('Apólice cadastrada com sucesso.'));
            $this->redirectRoute('policies.index', navigate: true);
        } catch (\Throwable $e) {
            Log::error('Policy creation failed', ['error' => $e->getMessage()]);
            Flux::toast(variant: 'danger', text: __('Erro ao cadastrar apólice. Tente novamente.'));
        }
    }
}; ?>

<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" :href="route('policies.index')" wire:navigate />
        <flux:heading size="xl">{{ __('Nova Apólice') }}</flux:heading>
    </div>

    @if ($renewed_from_id)
        <flux:callout variant="info" icon="arrow-path">
            {{ __('Renovando apólice') }} <strong>#{{ $renewed_from_id }}</strong>. {{ __('Os dados foram pré-preenchidos com base na apólice original.') }}
        </flux:callout>
    @endif

    <flux:card class="space-y-6">
        <flux:heading>{{ __('Dados da Apólice') }}</flux:heading>

        @include('pages.policies._form', ['readonly' => false])

        <div class="flex justify-end">
            <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled" x-submit-guard>
                {{ __('Salvar Apólice') }}
            </flux:button>
        </div>
    </flux:card>
</div>
