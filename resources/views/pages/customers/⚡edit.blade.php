<?php

use App\Concerns\CustomerValidationRules;
use App\Models\Customer;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Editar Cliente')] class extends Component {
    use CustomerValidationRules;

    public Customer $customer;

    public int $currentStep = 1;

    // Step 1 — customer data
    public string $name = '';
    public string $cpf = '';
    public string $email = '';
    public string $phone = '';
    public string $birth_date = '';

    // Step 2 — address
    public string $street = '';
    public string $zip_code = '';
    public string $neighborhood = '';
    public string $state = '';
    public string $city = '';
    public string $number = '';
    public string $complement = '';

    public function mount(Customer $customer): void
    {
        if (! $customer->belongsToUser(Auth::id())) {
            Log::warning('Unauthorized customer edit attempt', [
                'user_id' => Auth::id(),
                'customer_id' => $customer->id,
                'customer_user_id' => $customer->user_id,
            ]);
            abort(403);
        }

        $this->customer = $customer;
        $this->name = $customer->name;
        $this->cpf = $customer->cpf;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->birth_date = $customer->birth_date->format('d/m/Y');

        $address = $customer->activeAddress;
        if ($address) {
            $this->street = $address->street;
            $this->zip_code = $address->zip_code;
            $this->neighborhood = $address->neighborhood;
            $this->state = $address->state;
            $this->city = $address->city;
            $this->number = $address->number;
            $this->complement = $address->complement ?? '';
        }
    }

    public function nextStep(): void
    {
        $this->validate($this->step1Rules());
        $this->currentStep = 2;
    }

    public function previousStep(): void
    {
        $this->currentStep = 1;
    }

    public function save(): void
    {
        $rules = $this->step1Rules();
        if ($this->hasAddressData()) {
            $rules += $this->step2Rules();
        }
        $this->validate($rules);

        if (! $this->customer->belongsToUser(Auth::id())) {
            Log::warning('Unauthorized customer update attempt', [
                'user_id' => Auth::id(),
                'customer_id' => $this->customer->id,
                'customer_user_id' => $this->customer->user_id,
            ]);
            Flux::toast(variant: 'danger', text: __('Ação não autorizada.'));

            return;
        }

        try {
            $this->customer->update([
                'name' => Str::title($this->name),
                'cpf' => $this->cpf,
                'email' => $this->email,
                'phone' => $this->phone,
                'birth_date' => Carbon::createFromFormat('d/m/Y', $this->birth_date)->toDateString(),
            ]);

            $this->updateAddress();

            Flux::toast(variant: 'success', text: __('Cliente atualizado com sucesso.'));
            $this->redirectRoute('customers.index', navigate: true);
        } catch (\Throwable $e) {
            Log::error('Customer update failed', ['error' => $e->getMessage(), 'customer_id' => $this->customer->id]);
            Flux::toast(variant: 'danger', text: __('Erro ao atualizar cliente. Tente novamente.'));
        }
    }

    private function hasAddressData(): bool
    {
        return (bool) ($this->street || $this->zip_code || $this->neighborhood || $this->city || $this->number || $this->state);
    }

    private function updateAddress(): void
    {
        if (! $this->hasAddressData()) {
            return;
        }

        $addressData = [
            'street' => $this->street,
            'zip_code' => $this->zip_code,
            'neighborhood' => $this->neighborhood,
            'state' => strtoupper($this->state),
            'city' => $this->city,
            'number' => $this->number,
            'complement' => $this->complement ?: null,
        ];

        $currentActive = $this->customer->addresses()->where('status', true)->latest()->first();

        $hasChanged = ! $currentActive || collect($addressData)->some(
            fn ($value, $key) => $currentActive->$key !== $value
        );

        if ($hasChanged) {
            $this->customer->addresses()->where('status', true)->update(['status' => false]);
            $this->customer->addresses()->create([...$addressData, 'status' => true]);
        }
    }
}; ?>

<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" :href="route('customers.index')" wire:navigate />
        <flux:heading size="xl">{{ __('Editar Cliente') }}</flux:heading>
    </div>

    {{-- Step indicator --}}
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
            <div @class([
                'flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold',
                'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' => $currentStep >= 1,
                'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' => $currentStep < 1,
            ])>1</div>
            <span @class([
                'text-sm',
                'font-medium text-zinc-900 dark:text-white' => $currentStep >= 1,
                'text-zinc-400' => $currentStep < 1,
            ])>{{ __('Dados') }}</span>
        </div>
        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
        <div class="flex items-center gap-2">
            <div @class([
                'flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold',
                'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' => $currentStep >= 2,
                'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' => $currentStep < 2,
            ])>2</div>
            <span @class([
                'text-sm',
                'font-medium text-zinc-900 dark:text-white' => $currentStep >= 2,
                'text-zinc-400' => $currentStep < 2,
            ])>{{ __('Endereço') }}</span>
        </div>
    </div>

    <flux:card class="space-y-6">
        @if ($currentStep === 1)
            <div wire:key="step-1" class="contents">
            <flux:heading>{{ __('Dados do Cliente') }}</flux:heading>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <flux:input
                        wire:model.blur="name"
                        :label="__('Nome completo')"
                        :placeholder="__('João da Silva')"
                        required
                        autofocus
                    />
                </div>

                <flux:input
                    wire:model.blur="cpf"
                    x-on:input="maskCpf($el)"
                    :label="__('CPF')"
                    placeholder="000.000.000-00"
                    required
                />

                <flux:input
                    wire:model.blur="email"
                    type="email"
                    :label="__('E-mail')"
                    :placeholder="__('joao@exemplo.com')"
                    required
                />

                <flux:input
                    wire:model.blur="phone"
                    x-on:input="maskPhone($el)"
                    :label="__('Telefone')"
                    placeholder="(00) 00000-0000"
                    required
                />

                <flux:input
                    wire:model.blur="birth_date"
                    x-on:input="maskDate($el)"
                    :label="__('Data de nascimento')"
                    placeholder="DD/MM/AAAA"
                    required
                />
            </div>

            <div class="flex justify-end">
                <flux:button variant="primary" wire:click="nextStep">
                    {{ __('Próximo') }}
                </flux:button>
            </div>
            </div>
        @elseif ($currentStep === 2)
            <div wire:key="step-2" class="contents">
            <div>
                <flux:heading>{{ __('Endereço (opcional)') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">{{ __('Preencha abaixo ou deixe em branco para salvar sem endereço.') }}</flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <flux:input
                        wire:model.blur="street"
                        :label="__('Logradouro')"
                        :placeholder="__('Rua, Avenida, etc.')"
                    />
                </div>

                <flux:input
                    wire:model.blur="zip_code"
                    x-on:input="maskCep($el)"
                    :label="__('CEP')"
                    placeholder="00000-000"
                />

                <flux:input
                    wire:model.blur="number"
                    :label="__('Número')"
                    placeholder="123"
                />

                <div class="sm:col-span-2">
                    <flux:input
                        wire:model.blur="complement"
                        :label="__('Complemento')"
                        :placeholder="__('Apto, Bloco, etc.')"
                    />
                </div>

                <flux:input
                    wire:model.blur="neighborhood"
                    :label="__('Bairro')"
                />

                <flux:input
                    wire:model.blur="city"
                    :label="__('Cidade')"
                />

                <flux:input
                    wire:model.blur="state"
                    :label="__('Estado (UF)')"
                    placeholder="SP"
                    maxlength="2"
                />
            </div>

            <div class="flex justify-between">
                <flux:button variant="ghost" wire:click="previousStep">
                    {{ __('Voltar') }}
                </flux:button>
                <flux:button variant="primary" wire:click="save">
                    {{ __('Salvar Alterações') }}
                </flux:button>
            </div>
            </div>
        @endif
    </flux:card>
</div>
