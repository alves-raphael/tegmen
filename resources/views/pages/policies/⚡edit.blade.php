<?php

use App\Concerns\PolicyValidationRules;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Policy;
use App\Models\Vehicle;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Editar Apólice')] class extends Component {
    use PolicyValidationRules;

    public Policy $policy;

    // Read-only display fields (not editable)
    public string $policy_number = '';
    public string $customer_id = '';
    public string $vehicle_id = '';
    public string $insurer_id = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $premium = '';

    // Editable fields
    public string $commission_percentage = '';
    public string $commission_value = '';
    public string $notes = '';

    public function mount(Policy $policy): void
    {
        $policy->load('customer');

        if (! $policy->belongsToUser(Auth::id())) {
            Log::warning('Unauthorized policy edit attempt', [
                'user_id' => Auth::id(),
                'policy_id' => $policy->id,
            ]);
            abort(403);
        }

        $this->policy = $policy;
        $this->policy_number = $policy->policy_number;
        $this->customer_id = (string) $policy->customer_id;
        $this->vehicle_id = (string) $policy->vehicle_id;
        $this->insurer_id = (string) $policy->insurer_id;
        $this->start_date = $policy->start_date->format('d/m/Y');
        $this->end_date = $policy->end_date->format('d/m/Y');
        $this->premium = number_format($policy->premium, 2, ',', '.');
        $this->commission_percentage = $policy->commission_percentage !== null
            ? rtrim(rtrim(number_format($policy->commission_percentage, 2, '.', ''), '0'), '.')
            : '';
        $this->commission_value = $policy->commission_value !== null
            ? number_format($policy->commission_value, 2, ',', '.')
            : '';
        $this->notes = $policy->notes ?? '';
    }

    /**
     * @return Collection<int, Customer>
     */
    #[Computed]
    public function customers(): Collection
    {
        return Customer::where('id', (int) $this->customer_id)->get();
    }

    /**
     * @return Collection<int, InsuranceCompany>
     */
    #[Computed]
    public function insurers(): Collection
    {
        return InsuranceCompany::where('id', (int) $this->insurer_id)->get();
    }

    /**
     * @return Collection<int, Vehicle>
     */
    #[Computed]
    public function vehicles(): Collection
    {
        if (! $this->vehicle_id) {
            return new Collection;
        }

        return Vehicle::where('id', (int) $this->vehicle_id)->get();
    }

    public function updatedCommissionPercentage(): void
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
        // Strip commission_value mask before validation
        $this->commission_value = $this->commission_value
            ? (string) $this->parseCurrencyToFloat($this->commission_value)
            : '';

        $this->validate($this->editRules());

        try {
            $this->policy->update([
                'commission_percentage' => $this->commission_percentage !== '' ? (float) $this->commission_percentage : null,
                'commission_value' => $this->commission_value !== '' ? (float) $this->commission_value : null,
                'notes' => $this->notes ?: null,
            ]);

            Flux::toast(variant: 'success', text: __('Apólice atualizada com sucesso.'));
            $this->redirectRoute('policies.index', navigate: true);
        } catch (\Throwable $e) {
            Log::error('Policy update failed', ['error' => $e->getMessage(), 'policy_id' => $this->policy->id]);
            Flux::toast(variant: 'danger', text: __('Erro ao atualizar apólice. Tente novamente.'));
        }
    }
}; ?>

<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" :href="route('policies.index')" wire:navigate />
        <div>
            <flux:heading size="xl">{{ __('Editar Apólice') }}</flux:heading>
            <flux:text class="text-zinc-500">
                {{ __('Apenas comissão e observações podem ser alteradas.') }}
            </flux:text>
        </div>
    </div>

    <flux:card class="space-y-6">
        <flux:heading>{{ __('Dados da Apólice') }}</flux:heading>

        @include('pages.policies._form', ['readonly' => true])

        <div class="flex justify-end">
            <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled" x-submit-guard>
                {{ __('Salvar Alterações') }}
            </flux:button>
        </div>
    </flux:card>
</div>
