<?php

use App\Concerns\VehicleValidationRules;
use App\Models\Customer;
use App\Models\Vehicle;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Novo Veículo')] class extends Component {
    use VehicleValidationRules;

    public Customer $customer;

    public string $license_plate = '';
    public string $brand = '';
    public string $model = '';
    public string $model_year = '';
    public string $fipe = '';
    public string $usage = 'personal';
    public string $color = 'white';

    public function mount(Customer $customer): void
    {
        if (! $customer->belongsToUser(Auth::id())) {
            Log::warning('Unauthorized vehicle creation attempt', [
                'user_id' => Auth::id(),
                'customer_id' => $customer->id,
                'customer_user_id' => $customer->user_id,
            ]);
            abort(403);
        }

        $this->customer = $customer;
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function usageOptions(): array
    {
        return Vehicle::usageOptions();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function colorOptions(): array
    {
        return Vehicle::colorOptions();
    }

    public function save(): void
    {
        $this->validate($this->vehicleRules());

        try {
            $this->customer->vehicles()->create([
                'license_plate' => strtoupper($this->license_plate),
                'brand' => $this->brand,
                'model' => $this->model,
                'model_year' => $this->model_year,
                'fipe' => $this->fipe ?: null,
                'usage' => $this->usage,
                'color' => $this->color,
            ]);

            Flux::toast(variant: 'success', text: __('Veículo cadastrado com sucesso.'));
            $this->redirectRoute('vehicles.index', ['customer' => $this->customer->id], navigate: true);
        } catch (\Throwable $e) {
            Log::error('Vehicle creation failed', ['error' => $e->getMessage(), 'customer_id' => $this->customer->id]);
            Flux::toast(variant: 'danger', text: __('Erro ao cadastrar veículo. Tente novamente.'));
        }
    }
}; ?>

<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" :href="route('vehicles.index', $customer)" wire:navigate />
        <div>
            <flux:heading size="xl">{{ __('Novo Veículo') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ $customer->name }}</flux:text>
        </div>
    </div>

    <flux:card class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input
                wire:model.blur="license_plate"
                x-on:input="maskLicensePlate($el)"
                :label="__('Placa')"
                placeholder="ABC-1234"
                required
            />

            <flux:input
                wire:model.blur="model_year"
                :label="__('Ano do modelo')"
                placeholder="2024"
                maxlength="4"
                required
            />

            <flux:input
                wire:model.blur="brand"
                :label="__('Marca')"
                :placeholder="__('Volkswagen')"
                required
            />

            <flux:input
                wire:model.blur="model"
                :label="__('Modelo')"
                :placeholder="__('Gol')"
                required
            />

            <flux:select
                wire:model="usage"
                :label="__('Uso')"
                required
            >
                @foreach ($this->usageOptions as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select
                wire:model="color"
                :label="__('Cor')"
                required
            >
                @foreach ($this->colorOptions as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="sm:col-span-2">
                <flux:input
                    wire:model.blur="fipe"
                    :label="__('Código FIPE')"
                    :placeholder="__('001234-1 (opcional)')"
                />
            </div>
        </div>

        <div class="flex justify-end">
            <flux:button variant="primary" wire:click="save" x-submit-guard>
                {{ __('Salvar Veículo') }}
            </flux:button>
        </div>
    </flux:card>
</div>
