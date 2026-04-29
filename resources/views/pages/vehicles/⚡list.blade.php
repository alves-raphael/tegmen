<?php

use App\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Veículos')] class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<Vehicle>
     */
    #[Computed]
    public function vehicles(): LengthAwarePaginator
    {
        return Vehicle::query()
            ->with('customer')
            ->whereHas('customer', fn ($q) => $q->where('user_id', Auth::id()))
            ->when(
                $this->search,
                fn ($q) => $q
                    ->where('license_plate', 'like', "%{$this->search}%")
                    ->orWhere('brand', 'like', "%{$this->search}%")
                    ->orWhere('model', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$this->search}%"))
            )
            ->orderBy('brand')
            ->orderBy('model')
            ->paginate(15);
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="xl">{{ __('Veículos') }}</flux:heading>
    </div>

    <flux:input
        wire:model.live.debounce.300ms="search"
        icon="magnifying-glass"
        :placeholder="__('Buscar por placa, marca, modelo ou cliente...')"
        clearable
    />

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Cliente') }}</flux:table.column>
            <flux:table.column>{{ __('Marca / Modelo') }}</flux:table.column>
            <flux:table.column>{{ __('Placa') }}</flux:table.column>
            <flux:table.column>{{ __('Uso') }}</flux:table.column>
            <flux:table.column class="w-16">{{ __('Ações') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->vehicles as $vehicle)
                <flux:table.row :key="$vehicle->id">
                    <flux:table.cell>
                        <flux:link :href="route('customers.edit', $vehicle->customer)" wire:navigate>
                            {{ $vehicle->customer->name }}
                        </flux:link>
                    </flux:table.cell>
                    <flux:table.cell>{{ $vehicle->brand }} {{ $vehicle->model }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge variant="outline">{{ $vehicle->license_plate }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ Vehicle::usageOptions()[$vehicle->usage] ?? $vehicle->usage }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button
                            icon="pencil-square"
                            variant="ghost"
                            size="sm"
                            square
                            :href="route('vehicles.edit', [$vehicle->customer, $vehicle])"
                            :tooltip="__('Editar veículo')"
                            wire:navigate
                        />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-10 text-center">
                        <flux:text class="text-zinc-500">{{ __('Nenhum veículo encontrado.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if ($this->vehicles->hasPages())
        <div class="flex justify-center">
            {{ $this->vehicles->links() }}
        </div>
    @endif
</div>
