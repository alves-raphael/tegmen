<?php

use App\Models\Customer;
use App\Models\Vehicle;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Veículos')] class extends Component {
    public Customer $customer;

    public function mount(Customer $customer): void
    {
        if (! $customer->belongsToUser(Auth::id())) {
            Log::warning('Unauthorized vehicle listing attempt', [
                'user_id' => Auth::id(),
                'customer_id' => $customer->id,
                'customer_user_id' => $customer->user_id,
            ]);
            abort(403);
        }

        $this->customer = $customer;
    }

    /**
     * @return Collection<int, Vehicle>
     */
    #[Computed]
    public function vehicles(): Collection
    {
        return $this->customer->vehicles()->orderBy('model')->get();
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" icon="arrow-left" :href="route('customers.index')" wire:navigate />
                <flux:heading size="xl">{{ __('Veículos') }}</flux:heading>
            </div>
            <flux:text class="mt-1 text-zinc-500">
                {{ $customer->name }} / {{ $customer->email }}
            </flux:text>
        </div>
        <flux:button variant="primary" icon="plus" :href="route('vehicles.create', $customer)" wire:navigate>
            {{ __('Novo Veículo') }}
        </flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Modelo') }}</flux:table.column>
            <flux:table.column>{{ __('Marca') }}</flux:table.column>
            <flux:table.column>{{ __('Placa') }}</flux:table.column>
            <flux:table.column class="w-16">{{ __('Ações') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->vehicles as $vehicle)
                <flux:table.row :key="$vehicle->id">
                    <flux:table.cell>{{ $vehicle->model }}</flux:table.cell>
                    <flux:table.cell>{{ $vehicle->brand }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge variant="outline">{{ $vehicle->license_plate }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button
                            icon="pencil-square"
                            variant="ghost"
                            size="sm"
                            square
                            :href="route('vehicles.edit', [$customer, $vehicle])"
                            :tooltip="__('Editar veículo')"
                            wire:navigate
                        />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="py-10 text-center">
                        <flux:text class="text-zinc-500">{{ __('Nenhum veículo cadastrado.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
