<?php

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Clientes')] class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<Customer>
     */
    #[Computed]
    public function customers(): LengthAwarePaginator
    {
        return Customer::where('user_id', Auth::id())
            ->when(
                $this->search,
                fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->orderBy('name')
            ->paginate(15);
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="xl">{{ __('Clientes') }}</flux:heading>
        <flux:button variant="primary" icon="plus" :href="route('customers.create')" wire:navigate>
            {{ __('Novo Cliente') }}
        </flux:button>
    </div>

    <flux:input
        wire:model.live.debounce.300ms="search"
        icon="magnifying-glass"
        :placeholder="__('Buscar por nome ou e-mail...')"
        clearable
    />

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Nome') }}</flux:table.column>
            <flux:table.column>{{ __('E-mail') }}</flux:table.column>
            <flux:table.column class="w-20">{{ __('Ações') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->customers as $customer)
                <flux:table.row :key="$customer->id">
                    <flux:table.cell>{{ $customer->name }}</flux:table.cell>
                    <flux:table.cell>{{ $customer->email }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button
                                icon="pencil-square"
                                variant="ghost"
                                size="sm"
                                square
                                :href="route('customers.edit', $customer)"
                                :tooltip="__('Editar cliente')"
                                wire:navigate
                            />
                            <flux:button
                                icon="truck"
                                variant="ghost"
                                size="sm"
                                square
                                :href="route('vehicles.index', $customer)"
                                :tooltip="__('Ver veículos')"
                                wire:navigate
                            />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="py-10 text-center">
                        <flux:text class="text-zinc-500">{{ __('Nenhum cliente encontrado.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if ($this->customers->hasPages())
        <div class="flex justify-center">
            {{ $this->customers->links() }}
        </div>
    @endif
</div>
