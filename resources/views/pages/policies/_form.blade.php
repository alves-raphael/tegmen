{{--
    Shared policy form fields.
    $readonly (bool) — when true, all fields except commission_percentage, commission_value and notes are disabled.
--}}
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <flux:select
            wire:model.live="customer_id"
            searchable
            :disabled="$readonly"
            :label="__('Cliente')"
            :placeholder="__('Selecione um cliente...')"
            :required="! $readonly"
        >
            @foreach ($this->customers as $customer)
                <flux:select.option :value="$customer->id">{{ $customer->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="sm:col-span-2">
        <flux:select
            wire:model.live="insurer_id"
            searchable
            :disabled="$readonly"
            :label="__('Seguradora')"
            :placeholder="__('Selecione uma seguradora...')"
            :required="! $readonly"
        >
            @foreach ($this->insurers as $insurer)
                <flux:select.option :value="$insurer->id">{{ $insurer->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="sm:col-span-2">
        <flux:select
            wire:model.live="vehicle_id"
            searchable
            :disabled="$readonly || ! $customer_id"
            :label="__('Veículo')"
            :placeholder="($readonly || $customer_id) ? __('Selecione um veículo...') : __('Selecione um cliente primeiro')"
            :required="! $readonly"
        >
            @foreach ($this->vehicles as $vehicle)
                <flux:select.option :value="$vehicle->id">
                    {{ $vehicle->brand }} {{ $vehicle->model }} — {{ $vehicle->license_plate }}
                </flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="sm:col-span-2">
        <flux:input
            wire:model.blur="policy_number"
            :label="__('Número da Apólice')"
            :placeholder="__('Número fornecido pela seguradora')"
            :disabled="$readonly"
            :required="! $readonly"
        />
    </div>

    <flux:input
        wire:model.blur="start_date"
        x-on:input="{{ $readonly ? '' : 'maskDate($el)' }}"
        x-on:blur="{{ $readonly ? '' : 'prefillEndDate($el)' }}"
        :label="__('Data de início')"
        placeholder="DD/MM/AAAA"
        :disabled="$readonly"
        :required="! $readonly"
    />

    <flux:input
        wire:model.blur="end_date"
        x-on:input="{{ $readonly ? '' : 'maskDate($el)' }}"
        :label="__('Data de vencimento')"
        placeholder="DD/MM/AAAA"
        :disabled="$readonly"
        :required="! $readonly"
    />

    <flux:input
        wire:model.blur="premium"
        x-on:input="{{ $readonly ? '' : 'maskCurrency($el)' }}"
        :label="__('Prêmio (R$)')"
        placeholder="0,00"
        :disabled="$readonly"
        :required="! $readonly"
    />

    <flux:input
        wire:model.live="commission_percentage"
        :label="__('Comissão (%)')"
        placeholder="0"
        type="number"
        min="0"
        max="100"
        step="0.01"
    />

    <flux:input
        wire:model="commission_value"
        :label="__('Valor da Comissão (R$)')"
        placeholder="0,00"
        readonly
        class="bg-zinc-50 dark:bg-zinc-800"
    />

    <div class="sm:col-span-2">
        <flux:textarea
            wire:model.blur="notes"
            :label="__('Observações')"
            :placeholder="__('Observações adicionais sobre a apólice (opcional)')"
            rows="3"
        />
    </div>
</div>
