<?php

use App\Enums\PolicyStatus;
use App\Models\InsuranceCompany;
use App\Models\Policy;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Apólices')] class extends Component {
    use WithPagination;

    public string $statusFilter = 'ACTIVE';

    public string $insurerFilter = '';

    public bool $showCancelModal = false;

    public ?int $cancellingPolicyId = null;

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedInsurerFilter(): void
    {
        $this->resetPage();
    }

    /**
     * @return Collection<int, InsuranceCompany>
     */
    #[Computed]
    public function insurers(): Collection
    {
        return InsuranceCompany::query()
            ->whereHas('policies.customer', fn ($q) => $q->where('user_id', Auth::id()))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return LengthAwarePaginator<Policy>
     */
    #[Computed]
    public function policies(): LengthAwarePaginator
    {
        return Policy::query()
            ->with(['customer', 'vehicle', 'insurer'])
            ->whereHas('customer', fn ($q) => $q->where('user_id', Auth::id()))
            ->when(
                $this->statusFilter !== '',
                fn ($q) => $q->where('status', $this->statusFilter)
            )
            ->when(
                $this->insurerFilter !== '',
                fn ($q) => $q->where('insurer_id', $this->insurerFilter)
            )
            ->orderBy('end_date', 'asc')
            ->paginate(15);
    }

    public function openCancelModal(int $policyId): void
    {
        $this->cancellingPolicyId = $policyId;
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancellingPolicyId = null;
    }

    public function cancelPolicy(): void
    {
        $policy = Policy::with('customer')->find($this->cancellingPolicyId);

        if (! $policy || ! $policy->belongsToUser(Auth::id())) {
            Log::warning('Unauthorized policy cancellation attempt', [
                'user_id' => Auth::id(),
                'policy_id' => $this->cancellingPolicyId,
            ]);
            Flux::toast(variant: 'danger', text: __('Ação não autorizada.'));
            $this->closeCancelModal();

            return;
        }

        if (! $policy->status->isCancellable()) {
            Flux::toast(variant: 'danger', text: __('Esta apólice não pode ser cancelada.'));
            $this->closeCancelModal();

            return;
        }

        try {
            $policy->update([
                'status' => PolicyStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            $this->closeCancelModal();
            unset($this->policies);
            Flux::toast(variant: 'success', text: __('Apólice cancelada com sucesso.'));
        } catch (\Throwable $e) {
            Log::error('Policy cancellation failed', [
                'policy_id' => $this->cancellingPolicyId,
                'error' => $e->getMessage(),
            ]);
            Flux::toast(variant: 'danger', text: __('Erro ao cancelar apólice. Tente novamente.'));
            $this->closeCancelModal();
        }
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="xl">{{ __('Apólices') }}</flux:heading>
        <flux:button variant="primary" icon="plus" :href="route('policies.create')" wire:navigate>
            {{ __('Nova Apólice') }}
        </flux:button>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row">
        <div class="w-full sm:w-52">
            <flux:select wire:model.live="statusFilter" :label="__('Status')">
                <flux:select.option value="">{{ __('Todos os status') }}</flux:select.option>
                <flux:select.option value="ACTIVE">{{ __('Ativas') }}</flux:select.option>
                <flux:select.option value="RENEWED">{{ __('Renovadas') }}</flux:select.option>
                <flux:select.option value="CANCELLED">{{ __('Canceladas') }}</flux:select.option>
                <flux:select.option value="EXPIRED">{{ __('Expiradas') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="w-full sm:w-64">
            <flux:select wire:model.live="insurerFilter" :label="__('Seguradora')">
                <flux:select.option value="">{{ __('Todas as seguradoras') }}</flux:select.option>
                @foreach ($this->insurers as $insurer)
                    <flux:select.option :value="$insurer->id">{{ $insurer->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Nº Apólice') }}</flux:table.column>
                <flux:table.column>{{ __('Cliente') }}</flux:table.column>
                <flux:table.column>{{ __('Marca / Modelo') }}</flux:table.column>
                <flux:table.column>{{ __('Seguradora') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Vencimento') }}</flux:table.column>
                <flux:table.column>{{ __('Prêmio') }}</flux:table.column>
                <flux:table.column>{{ __('Dias p/ vencer') }}</flux:table.column>
                <flux:table.column class="w-24">{{ __('Ações') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->policies as $policy)
                    <flux:table.row :key="$policy->id">
                        <flux:table.cell>
                            <flux:badge variant="outline">{{ $policy->policy_number }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $policy->customer->name }}</flux:table.cell>
                        <flux:table.cell>{{ $policy->vehicle->brand }} {{ $policy->vehicle->model }}</flux:table.cell>
                        <flux:table.cell>{{ $policy->insurer->name }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$policy->status->badgeColor()">
                                {{ $policy->status->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $policy->end_date->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell>
                            R$ {{ number_format($policy->premium, 2, ',', '.') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @php
                                $days = (int) now()->startOfDay()->diffInDays($policy->end_date->startOfDay(), false);
                            @endphp
                            <flux:badge :color="$days > 30 ? 'lime' : ($days > 15 ? 'amber' : 'red')">
                                {{ $days < 0 ? __('Expirado') : $days.' '.__('dias') }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-1">
                                <flux:button
                                    icon="pencil-square"
                                    variant="ghost"
                                    size="sm"
                                    square
                                    :href="route('policies.edit', $policy)"
                                    :tooltip="__('Editar apólice')"
                                    wire:navigate
                                />
                                @if ($policy->status->isRenewable())
                                    <flux:button
                                        icon="arrow-path"
                                        variant="ghost"
                                        size="sm"
                                        square
                                        :href="route('policies.create', ['renew_from' => $policy->id])"
                                        :tooltip="__('Renovar apólice')"
                                        wire:navigate
                                    />
                                @endif
                                @if ($policy->status->isCancellable())
                                    <flux:button
                                        icon="x-circle"
                                        variant="danger"
                                        size="sm"
                                        square
                                        :tooltip="__('Cancelar apólice')"
                                        wire:click="openCancelModal({{ $policy->id }})"
                                    />
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="10" class="py-10 text-center">
                            <flux:text class="text-zinc-500">{{ __('Nenhuma apólice encontrada.') }}</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    @if ($this->policies->hasPages())
        <div class="flex justify-center">
            {{ $this->policies->links() }}
        </div>
    @endif

    <flux:modal wire:model="showCancelModal" class="max-w-sm">
        <div class="space-y-4">
            <flux:heading>{{ __('Cancelar Apólice') }}</flux:heading>
            <flux:text>
                {{ __('Tem certeza que deseja cancelar esta apólice? Esta ação não pode ser desfeita.') }}
            </flux:text>
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeCancelModal">
                    {{ __('Voltar') }}
                </flux:button>
                <flux:button variant="danger" wire:click="cancelPolicy" wire:loading.attr="disabled" x-submit-guard>
                    {{ __('Confirmar Cancelamento') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
