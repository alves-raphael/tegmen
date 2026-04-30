<?php

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    /**
     * @return Collection<int, Customer>
     */
    #[Computed]
    public function customers(): Collection
    {
        return Customer::where('user_id', Auth::id())
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', now()->month)
            ->orderByRaw('DAY(birth_date) ASC')
            ->get();
    }
}; ?>

<div class="flex h-full flex-col overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
    <div class="flex items-center gap-2 border-b border-neutral-200 px-4 py-3 dark:border-neutral-700">
        <flux:icon.cake class="size-5 text-pink-500" />
        <flux:heading size="sm">{{ __('Aniversariantes do Mês') }}</flux:heading>
    </div>

    <div class="flex-1 overflow-y-auto">
        @forelse ($this->customers as $customer)
            <div
                class="flex items-center justify-between px-4 py-2.5 {{ !$loop->last ? 'border-b border-neutral-100 dark:border-neutral-800' : '' }}">
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="flex size-8 shrink-0 items-center justify-center rounded-full bg-pink-100 dark:bg-pink-900/40">
                        <span class="text-xs font-semibold text-pink-600 dark:text-pink-400">
                            {{ $customer->birth_date->format('d') }}
                        </span>
                    </div>
                    <flux:text class="truncate font-medium text-sm">{{ $customer->name }}</flux:text>
                </div>
                <flux:badge size="sm" color="pink" variant="pill">
                    {{ now()->year - $customer->birth_date->year }} {{ __('anos') }}
                </flux:badge>
            </div>
        @empty
            <div class="flex h-full flex-col items-center justify-center gap-2 py-10 text-center">
                <flux:icon.cake class="size-8 text-neutral-300 dark:text-neutral-600" />
                <flux:text class="text-sm text-zinc-400">{{ __('Nenhum aniversariante este mês.') }}</flux:text>
            </div>
        @endforelse
    </div>
</div>